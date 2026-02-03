<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\QrCode;
use App\Models\Coupon;
use App\Services\MercadoPagoService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class SubscriptionController extends Controller
{
    protected MercadoPagoService $mercadopagoService;
    protected EmailService $emailService;

    public function __construct(MercadoPagoService $mercadopagoService, EmailService $emailService)
    {
        $this->mercadopagoService = $mercadopagoService;
        $this->emailService = $emailService;
    }

    /**
     * Mostrar página de suscripciones
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes una compañía asignada. Por favor, contacta al soporte.');
        }

        $currentSubscription = $company->activeSubscription;
        $currentPlan = $company->currentPlan;

        // Obtener todos los planes disponibles
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $freePlan = SubscriptionPlan::getFreePlan();
        $premiumPlan = SubscriptionPlan::getPremiumPlan();

        // Información del plan actual y límites
        $currentLimits = [
            'restaurants' => [
                'current' => $company->getRestaurantsCount(),
                'limit' => $company->getRestaurantLimit(),
                'remaining' => $company->getRemainingRestaurants(),
            ],
            'users' => [
                'current' => $company->getUsersCount(),
                'limit' => $company->getUserLimit(),
                'remaining' => $company->getRemainingUsers(),
            ],
            'qr_codes' => [
                'current' => $company->getTotalQrCodesCount(),
                'limit' => $company->getQrCodeLimit(),
                'remaining' => $company->getRemainingQrCodes(),
            ],
        ];

        return view('admin.subscription', compact(
            'company',
            'currentSubscription',
            'currentPlan',
            'plans',
            'freePlan',
            'premiumPlan',
            'currentLimits'
        ));
    }

    /**
     * Crear intento de suscripción (crear payment preference)
     */
    public function createIntent(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'sometimes|in:monthly,annual',
                'coupon_code' => 'nullable|string|exists:coupons,code',
            ]);

            $user = Auth::user();
            $company = $user->company;
            $plan = SubscriptionPlan::findOrFail($request->plan_id);
            $billingCycle = $request->input('billing_cycle', 'monthly');
            $couponCode = $request->input('coupon_code');

            // Verificar que no esté ya en ese plan activo
            // TODO: Mejorar check para diferenciar anual/mensual si es necesario
            if ($company->plan_id === $plan->id && $company->isOnPremiumPlan()) {
                // Si el ciclo es diferente, permitimos el cambio (upgrade/downgrade de ciclo) 
                // pero esto requeriría lógica extra para cancelar la anterior.
                // Por simplicidad, si ya es premium, bloqueamos por ahora salvo que sea upgrade de free
                 return response()->json([
                    'message' => 'Ya tienes este plan activo',
                    'current_plan' => $plan->slug,
                ], 400);
            }

            // Si es plan FREE, asignar directamente sin pago
            if ($plan->slug === 'free') {
                $this->assignFreePlan($company, $plan);
                return response()->json([
                    'message' => 'Plan FREE asignado correctamente',
                    'plan' => $plan->slug,
                ]);
            }

            $mpPlan = null;
            $coupon = null;

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if (!$coupon || !$coupon->isValid()) {
                     return response()->json(['message' => 'Cupón inválido o expirado'], 400);
                }
                $mpPlan = $this->mercadopagoService->createCouponPlan($plan, $coupon, $billingCycle);
            } else {
                $mpPlan = $this->mercadopagoService->createOrGetPreapprovalPlan($plan, $billingCycle);
            }

            // El plan de preapproval tiene un init_point que redirige directamente al checkout
            if (!isset($mpPlan['init_point'])) {
                throw new Exception('No se recibió init_point del plan de MercadoPago');
            }

            // Guardar temporalmente la intención
            $cacheKey = "subscription_intent_{$mpPlan['id']}_{$company->id}";
            cache()->put($cacheKey, [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'mp_plan_id' => $mpPlan['id'],
                'billing_cycle' => $billingCycle,
                'coupon_id' => $coupon ? $coupon->id : null,
            ], now()->addHour());

            Log::info('Subscription intent created', [
                'cache_key' => $cacheKey,
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'coupon' => $couponCode,
            ]);

            return response()->json([
                'plan_id' => $plan->id,
                'mp_plan_id' => $mpPlan['id'],
                'init_point' => $mpPlan['init_point'],
                'sandbox_init_point' => $mpPlan['sandbox_init_point'] ?? null,
                'public_key' => $this->mercadopagoService->getPublicKey(),
                'plan' => $plan->slug,
                'billing_cycle' => $billingCycle,
                'message' => 'Plan creado correctamente. Redirigiendo al checkout...',
            ]);
        } catch (Exception $e) {
            Log::error('Error creating subscription intent', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al crear intento de suscripción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear suscripción con token de tarjeta
     */
    public function createWithCardToken(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'card_token' => 'required|string',
            ]);

            $user = Auth::user();
            $company = $user->company;
            $plan = SubscriptionPlan::findOrFail($request->plan_id);

            if (!$company) {
                return response()->json([
                    'message' => 'No tienes una compañía asignada',
                ], 400);
            }

            // Verificar que no esté ya en ese plan activo
            if ($company->plan_id === $plan->id && $company->isOnPremiumPlan()) {
                return response()->json([
                    'message' => 'Ya tienes este plan activo',
                    'current_plan' => $plan->slug,
                ], 400);
            }

            // Crear la suscripción usando el token de tarjeta
            $preapproval = $this->mercadopagoService->createSubscriptionWithCardToken(
                $company,
                $plan,
                $request->card_token
            );

            // Crear registro de suscripción local
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'mp_subscription_id' => $preapproval['id'] ?? null,
                'mp_preapproval_id' => $preapproval['id'] ?? null,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            // Actualizar company
            $company->update([
                'plan_id' => $plan->id,
                'subscription_id' => $subscription->id,
            ]);

            Log::info('Subscription created with card token', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'mp_preapproval_id' => $preapproval['id'] ?? null,
            ]);

            // Enviar email de suscripción comprada (solo para plan premium)
            if ($plan->slug === 'premium') {
                $subscription->load('plan');
                $this->emailService->sendSubscriptionPurchasedEmail($company, $subscription);
            }

            return response()->json([
                'message' => 'Suscripción creada correctamente',
                'subscription_id' => $subscription->id,
                'plan' => $plan->slug,
            ]);
        } catch (Exception $e) {
            Log::error('Error creating subscription with card token', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al crear suscripción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asignar plan FREE directamente
     */
    private function assignFreePlan(Company $company, SubscriptionPlan $plan): void
    {
        // Si tiene suscripción activa, cancelarla
        if ($company->activeSubscription && $company->activeSubscription->isActive()) {
            $company->activeSubscription->cancel();
        }

        // Crear suscripción FREE
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addYear(), // FREE no expira
        ]);

        $company->update([
            'plan_id' => $plan->id,
            'subscription_id' => $subscription->id,
        ]);

        // Bloquear recursos que exceden los límites del plan free
        $this->blockExcessResourcesForFreePlan($company, $plan);
    }

    /**
     * Bloquear recursos que exceden los límites del plan free
     */
    private function blockExcessResourcesForFreePlan(Company $company, SubscriptionPlan $freePlan): void
    {
        $limits = $freePlan->getLimits();

        // Bloquear restaurants excedentes (mantener los más antiguos activos)
        $restaurantLimit = $limits['restaurants'] ?? 1;
        $restaurants = $company->restaurants()->orderBy('created_at', 'asc')->get();
        $activeCount = 0;

        foreach ($restaurants as $restaurant) {
            if ($activeCount < $restaurantLimit) {
                // Mantener activo (los más antiguos)
                if ($restaurant->is_blocked) {
                    $restaurant->unblock();
                }
                $activeCount++;
            } else {
                // Bloquear (los más nuevos)
                if (!$restaurant->is_blocked) {
                    $restaurant->block('subscription_downgrade');
                }
            }
        }

        // Bloquear usuarios excedentes (excepto owners)
        $userLimit = $limits['users'] ?? 1;
        $users = $company->users()->where('is_owner', false)->orderBy('created_at', 'asc')->get();
        $activeUserCount = 0;

        foreach ($users as $user) {
            if ($activeUserCount < $userLimit) {
                if ($user->is_blocked) {
                    $user->unblock();
                }
                $activeUserCount++;
            } else {
                if (!$user->is_blocked) {
                    $user->block('subscription_downgrade');
                }
            }
        }

        // Bloquear QR codes excedentes (mantener los más antiguos activos)
        $qrCodeLimit = $limits['qr_codes'] ?? 2;
        $qrCodes = QrCode::whereIn('restaurant_id', $company->restaurants->pluck('id'))
            ->orderBy('created_at', 'asc')
            ->get();

        $activeQrCount = 0;
        foreach ($qrCodes as $qrCode) {
            if ($activeQrCount < $qrCodeLimit) {
                if ($qrCode->is_blocked) {
                    $qrCode->unblock();
                }
                $activeQrCount++;
            } else {
                if (!$qrCode->is_blocked) {
                    $qrCode->block('subscription_downgrade');
                }
            }
        }

        Log::info('Resources blocked after switching to free plan', [
            'company_id' => $company->id,
            'restaurants_blocked' => $restaurants->count() - $activeCount,
            'users_blocked' => $users->count() - $activeUserCount,
            'qr_codes_blocked' => $qrCodes->count() - $activeQrCount,
        ]);
    }

    /**
     * Desbloquear todos los recursos de una company (cuando se cambia a plan premium).
     */
    private function unblockAllResources(Company $company): void
    {
        // Desbloquear todos los restaurants
        $company->restaurants()->where('is_blocked', true)->each(function ($restaurant) {
            $restaurant->unblock();
        });

        // Desbloquear todos los QR codes
        $restaurantIds = $company->restaurants->pluck('id');
        if ($restaurantIds->isNotEmpty()) {
            QrCode::whereIn('restaurant_id', $restaurantIds)
                ->where('is_blocked', true)
                ->each(function ($qrCode) {
                    $qrCode->unblock();
                });
        }

        // Desbloquear todos los usuarios (excepto owners)
        $company->users()
            ->where('is_owner', false)
            ->where('is_blocked', true)
            ->each(function ($user) {
                $user->unblock();
            });

        Log::info('All resources unblocked after switching to premium plan', [
            'company_id' => $company->id,
        ]);
    }

    /**
     * Callback de éxito después del pago
     */
    public function success(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('admin.subscription')
                ->with('error', 'No tienes una compañía asignada.');
        }

        // Verificar parámetros de la URL
        $preapprovalId = $request->query('preapproval_id');
        $preapprovalPlanId = $request->query('preapproval_plan_id');
        $status = $request->query('status');

        Log::info('Subscription payment success callback', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'preapproval_id' => $preapprovalId,
            'preapproval_plan_id' => $preapprovalPlanId,
            'status' => $status,
            'all_query_params' => $request->query(),
        ]);

        // Verificar si ya existe una suscripción activa (puede haber sido creada por el webhook)
        $subscription = $company->activeSubscription;
        $currentPlan = $company->currentPlan;

        if ($subscription && $subscription->isActive() && $currentPlan && $currentPlan->slug === 'premium') {
            // La suscripción ya está activa (probablemente creada por el webhook)
            return redirect()->route('admin.subscription')
                ->with('success', '¡Suscripción activada correctamente! Tu plan premium ya está activo.');
        }

        // Si no hay suscripción activa, intentar obtener información del preapproval
        if ($preapprovalId) {
            try {
                $preapproval = $this->mercadopagoService->getPreapproval($preapprovalId);

                if (!$preapproval) {
                    Log::warning('Preapproval not found in success callback', ['preapproval_id' => $preapprovalId]);
                    return redirect()->route('admin.subscription')
                        ->with('error', 'No se pudo encontrar la información de la suscripción. Por favor, contacta a soporte.');
                }

                Log::info('Preapproval retrieved in success callback', [
                    'preapproval_id' => $preapprovalId,
                    'status' => $preapproval['status'] ?? null,
                    'preapproval_plan_id' => $preapproval['preapproval_plan_id'] ?? null,
                ]);

                $preapprovalStatus = $preapproval['status'] ?? null;
                $preapprovalPlanId = $preapproval['preapproval_plan_id'] ?? $preapprovalPlanId;

                if (($preapprovalStatus === 'authorized' || $preapprovalStatus === 'active') && $preapprovalPlanId) {
                    // Buscar el plan usando el preapproval_plan_id
                    $plan = SubscriptionPlan::where('mp_plan_id', $preapprovalPlanId)->first();

                    if (!$plan) {
                        Log::warning('Plan not found for preapproval_plan_id in success callback', [
                            'preapproval_plan_id' => $preapprovalPlanId,
                            'preapproval_id' => $preapprovalId,
                        ]);
                        return redirect()->route('admin.subscription')
                            ->with('error', 'No se pudo encontrar el plan de suscripción. Por favor, contacta a soporte.');
                    }

                    // Verificar si la suscripción ya existe (puede haberse creado por webhook)
                    $existingSubscription = Subscription::where('mp_preapproval_id', $preapprovalId)->first();

                    if (!$existingSubscription) {
                        // Crear la suscripción local si no existe
                        $subscription = Subscription::create([
                            'company_id' => $company->id,
                            'plan_id' => $plan->id,
                            'status' => 'active',
                            'mp_subscription_id' => $preapprovalId,
                            'mp_preapproval_id' => $preapprovalId,
                            'current_period_start' => now(),
                            'current_period_end' => now()->addMonth(),
                        ]);

                            // Actualizar company
                            $company->update([
                                'plan_id' => $plan->id,
                                'subscription_id' => $subscription->id,
                            ]);

                            // Si es plan premium, desbloquear todos los recursos
                            if ($plan->slug === 'premium') {
                                $this->unblockAllResources($company);
                            }

                            Log::info('Subscription created from success callback', [
                                'subscription_id' => $subscription->id,
                                'company_id' => $company->id,
                                'plan_id' => $plan->id,
                                'preapproval_id' => $preapprovalId,
                                'plan_slug' => $plan->slug,
                            ]);

                            // Enviar email de suscripción comprada (solo para plan premium)
                            if ($plan->slug === 'premium') {
                                $subscription->load('plan');
                                $this->emailService->sendSubscriptionPurchasedEmail($company, $subscription);
                            }
                    } else {
                        // La suscripción ya existe, actualizar company si es necesario
                        if ($company->plan_id != $plan->id || $company->subscription_id != $existingSubscription->id) {
                            $company->update([
                                'plan_id' => $plan->id,
                                'subscription_id' => $existingSubscription->id,
                            ]);

                            Log::info('Company updated with existing subscription', [
                                'company_id' => $company->id,
                                'plan_id' => $plan->id,
                                'subscription_id' => $existingSubscription->id,
                            ]);
                        }
                    }

                    return redirect()->route('admin.subscription')
                        ->with('success', '¡Suscripción activada correctamente! Tu plan premium ya está activo.');
                } else {
                    Log::warning('Preapproval status not authorized/active in success callback', [
                        'status' => $preapprovalStatus,
                        'preapproval_id' => $preapprovalId,
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Error processing subscription success callback', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'preapproval_id' => $preapprovalId,
                ]);
            }
        }

        // Si llegamos aquí, esperar un momento y verificar nuevamente (el webhook puede tardar)
        // Por ahora, mostrar un mensaje indicando que se está procesando
        return redirect()->route('admin.subscription')
            ->with('info', 'Tu pago está siendo procesado. La suscripción se activará en unos momentos. Si no se activa automáticamente, por favor recarga la página.');
    }

    /**
     * Callback de fallo después del pago
     */
    public function failure(Request $request)
    {
        Log::warning('Subscription payment failure callback', [
            'query' => $request->query(),
        ]);

        return redirect()->route('admin.subscription')
            ->with('error', 'Hubo un problema con el pago. Por favor, intenta nuevamente.');
    }

    /**
     * Callback de pago pendiente
     */
    public function pending(Request $request)
    {
        Log::info('Subscription payment pending callback', [
            'query' => $request->query(),
        ]);

        return redirect()->route('admin.subscription')
            ->with('info', 'Tu pago está siendo procesado. Te notificaremos cuando se complete.');
    }

    /**
     * Obtener información de la suscripción actual
     */
    public function show(Request $request): JsonResponse
    {
        $company = Auth::user()->company;

        $subscription = $company->activeSubscription;
        $plan = $company->currentPlan;

        return response()->json([
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'cancelled_at' => $subscription->cancelled_at,
                'ends_at' => $subscription->ends_at,
            ] : null,
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'limits' => $plan->getLimits(),
                'features' => $plan->getFeatures(),
            ] : null,
            'is_premium' => $company->isOnPremiumPlan(),
            'is_free' => $company->isOnFreePlan(),
            'limits' => [
                'restaurants' => [
                    'current' => $company->getRestaurantsCount(),
                    'limit' => $company->getRestaurantLimit(),
                    'remaining' => $company->getRemainingRestaurants(),
                ],
                'users' => [
                    'current' => $company->getUsersCount(),
                    'limit' => $company->getUserLimit(),
                    'remaining' => $company->getRemainingUsers(),
                ],
                'qr_codes' => [
                    'current' => $company->getTotalQrCodesCount(),
                    'limit' => $company->getQrCodeLimit(),
                    'remaining' => $company->getRemainingQrCodes(),
                ],
            ],
        ]);
    }

    /**
     * Cancelar suscripción
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $company = Auth::user()->company;
            $subscription = $company->activeSubscription;

            if (!$subscription || !$subscription->isActive()) {
                return response()->json([
                    'message' => 'No tienes una suscripción activa para cancelar',
                ], 400);
            }

            // Cancelar preapproval en MercadoPago
            if ($subscription->mp_preapproval_id) {
                $this->mercadopagoService->cancelPreapproval($subscription->mp_preapproval_id);
            }

            // Cancelar suscripción localmente
            $endsAt = $subscription->current_period_end;
            $subscription->cancel($endsAt);

            // Degradar a plan FREE
            $freePlan = SubscriptionPlan::getFreePlan();
            if ($freePlan) {
                $company->update(['plan_id' => $freePlan->id]);
            }

            Log::info('Subscription cancelled', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
            ]);

            // Enviar email de suscripción cancelada
            $subscription->load('plan');
            $this->emailService->sendSubscriptionCancelledEmail($company, $subscription);

            return response()->json([
                'message' => 'Suscripción cancelada correctamente. Mantendrás acceso hasta ' . $endsAt->format('d/m/Y'),
                'ends_at' => $endsAt,
            ]);
        } catch (Exception $e) {
            Log::error('Error cancelling subscription', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al cancelar suscripción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reactivar suscripción cancelada
     */
    public function reactivate(Request $request): JsonResponse
    {
        try {
            $company = Auth::user()->company;
            $subscription = $company->activeSubscription;

            if (!$subscription || !$subscription->isCancelled()) {
                return response()->json([
                    'message' => 'No tienes una suscripción cancelada para reactivar',
                ], 400);
            }

            // Si el período ya expiró, necesitamos crear una nueva suscripción
            if ($subscription->ends_at && $subscription->ends_at->isPast()) {
                return response()->json([
                    'message' => 'Tu período de suscripción ha expirado. Por favor, crea una nueva suscripción.',
                    'requires_new_subscription' => true,
                ], 400);
            }

            // Reactivar preapproval en MercadoPago si existe
            // Nota: Esto puede requerir crear un nuevo preapproval dependiendo de la política de MP
            // Por ahora, reactivamos localmente
            $subscription->reactivate();

            // Restaurar plan premium
            $premiumPlan = SubscriptionPlan::getPremiumPlan();
            if ($premiumPlan) {
                $company->update(['plan_id' => $premiumPlan->id]);

                // Desbloquear todos los recursos cuando se reactiva a premium
                $this->unblockAllResources($company);
            }

            return response()->json([
                'message' => 'Suscripción reactivada correctamente',
                'subscription' => $subscription->fresh(),
            ]);
        } catch (Exception $e) {
            Log::error('Error reactivating subscription', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al reactivar suscripción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener historial de pagos
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        $company = Auth::user()->company;
        $subscription = $company->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'payments' => [],
            ]);
        }

        $payments = $subscription->payments()
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'mp_payment_id' => $payment->mp_payment_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date,
                ];
            });

        return response()->json([
            'payments' => $payments,
        ]);
    }
}

