<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\QrCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Exception;

class MercadoPagoService
{
    private string $accessToken;
    private string $publicKey;
    private string $appId;
    private string $baseUrl;
    private bool $isProduction;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
        $this->publicKey = config('services.mercadopago.public_key');
        $this->appId = config('services.mercadopago.app_id');
        $this->isProduction = config('services.mercadopago.environment') === 'production';
        $this->baseUrl = $this->isProduction
            ? 'https://api.mercadopago.com'
            : 'https://api.mercadopago.com';

        // Log de credenciales para depuración (sin mostrar el token completo por seguridad)
        Log::info('MercadoPagoService - Credenciales cargadas', [
            'environment' => $this->isProduction ? 'production' : 'sandbox',
            'access_token_prefix' => substr($this->accessToken, 0, 20) . '...',
            'public_key_prefix' => substr($this->publicKey, 0, 20) . '...',
            'app_id' => $this->appId,
            'base_url' => $this->baseUrl,
            'env_mp_environment' => env('MP_ENVIRONMENT'),
            'env_mp_app_id' => env('MP_APP_ID'),
        ]);
    }

    /**
     * Crear o obtener un plan de preapproval para suscripciones
     * Este método crea un plan si no existe, o devuelve el existente
     */
    public function createOrGetPreapprovalPlan(SubscriptionPlan $plan, string $billingCycle = 'monthly'): array
    {
        try {
            // Verificar si ya existe un plan MP para este plan (dependiendo del ciclo)
            $mpPlanId = $billingCycle === 'annual' ? $plan->mp_annual_plan_id : $plan->mp_plan_id;

            if ($mpPlanId) {
                // Intentar obtener el plan existente
                try {
                    $existingPlan = $this->getPreapprovalPlan($mpPlanId);
                    if ($existingPlan) {
                        // Verificar que el plan esté activo
                        if ($existingPlan['status'] === 'active') {
                            // El plan existe y está activo
                            // Si fue creado con credenciales diferentes, MercadoPago lo rechazará
                            // cuando se intente usar, pero por ahora lo retornamos
                            Log::info('Using existing MP plan', [
                                'mp_plan_id' => $mpPlanId,
                                'plan_id' => $plan->id,
                                'plan_collector_id' => $existingPlan['collector_id'] ?? null,
                                'plan_status' => $existingPlan['status'],
                            ]);
                            return $existingPlan;
                        } else {
                            // El plan no está activo, crear uno nuevo
                            Log::warning('MP Plan is not active, will create new one', [
                                'mp_plan_id' => $mpPlanId,
                                'plan_id' => $plan->id,
                                'plan_status' => $existingPlan['status'] ?? 'unknown',
                            ]);
                            if ($billingCycle === 'annual') {
                                $plan->update(['mp_annual_plan_id' => null]);
                            } else {
                                $plan->update(['mp_plan_id' => null]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    // El plan no existe, fue eliminado, o hay un error accediendo a él
                    Log::warning('MP Plan not found or error accessing it, will create new one', [
                        'mp_plan_id' => $mpPlanId,
                        'plan_id' => $plan->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Limpiar el mp_plan_id para evitar intentar usar un plan inválido
                    if ($billingCycle === 'annual') {
                        $plan->update(['mp_annual_plan_id' => null]);
                    } else {
                        $plan->update(['mp_plan_id' => null]);
                    }
                }
            }

            // Crear nuevo plan de preapproval
            // Obtener URL pública (ngrok en desarrollo, APP_URL en producción)
            $configUrl = config('app.url');
            $publicUrl = env('MERCADOPAGO_PUBLIC_URL', $configUrl);

            // Si la URL configurada es localhost, usar la URL de ngrok por defecto
            // MercadoPago no acepta URLs de localhost porque no son accesibles desde internet
            if (empty($publicUrl) ||
                str_contains($publicUrl, 'localhost') ||
                str_contains($publicUrl, '127.0.0.1') ||
                str_starts_with($publicUrl, 'http://localhost') ||
                str_starts_with($publicUrl, 'http://127.0.0.1')) {
                // Usar URL de ngrok por defecto para desarrollo
                $publicUrl = 'https://0f0101abbe45.ngrok-free.app';
            }

            $baseUrl = rtrim($publicUrl, '/');
            $backUrl = $baseUrl . '/subscriptions/success';

            // Logs de depuración
            Log::info('MercadoPago Preapproval Plan - Debug URLs', [
                'config_app_url' => $configUrl,
                'mercado_pago_public_url_env' => env('MERCADOPAGO_PUBLIC_URL'),
                'final_public_url' => $publicUrl,
                'final_base_url' => $baseUrl,
                'final_back_url' => $backUrl,
                'back_url_valid' => filter_var($backUrl, FILTER_VALIDATE_URL) !== false,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
            ]);

            $price = $billingCycle === 'annual' ? $plan->price_annual : $plan->price;
            $frequency = $billingCycle === 'annual' ? 12 : 1;
            $reason = "Cartify Premium - {$plan->name} (" . ($billingCycle === 'annual' ? 'Anual' : 'Mensual') . ")";

            $preapprovalPlanData = [
                'reason' => $reason,
                'auto_recurring' => [
                    'frequency' => $frequency,
                    'frequency_type' => 'months',
                    'repetitions' => null, // Sin límite de repeticiones (suscripción indefinida)
                    // No especificamos billing_day en el plan para permitir que cada suscripción tenga su propio día
                    'billing_day_proportional' => false, // Sin prorateo - siempre se cobra el plan completo
                    'transaction_amount' => (float) $price,
                    'currency_id' => 'UYU', // TODO: obtener de company o plan
                ],
                'payment_methods_allowed' => [
                    'payment_types' => [
                        ['id' => 'credit_card'],
                        ['id' => 'debit_card'],
                        ['id' => 'account_money'],  // Dinero en cuenta de MercadoPago
                    ],
                    'payment_methods' => [],  // Array vacío permite todos los métodos
                ],
                'back_url' => $backUrl,
            ];

            // Log del payload completo que se enviará
            Log::info('MercadoPago Preapproval Plan - Request Payload', [
                'endpoint' => "{$this->baseUrl}/preapproval_plan",
                'payload' => $preapprovalPlanData,
                'back_url_in_payload' => $preapprovalPlanData['back_url'],
                'access_token_prefix' => substr($this->accessToken, 0, 20) . '...',
                'is_production' => $this->isProduction,
                'app_id' => $this->appId,
                'environment_config' => config('services.mercadopago.environment'),
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'plan_' . $plan->id . '_' . time(),
            ])->post("{$this->baseUrl}/preapproval_plan", $preapprovalPlanData);

            // Log de la respuesta
            Log::info('MercadoPago Preapproval Plan - Response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                throw new Exception('Error creating preapproval plan: ' . $response->body());
            }

            $planData = $response->json();

            // Guardar el ID del plan de MP en el plan local
            if ($billingCycle === 'annual') {
                $plan->update(['mp_annual_plan_id' => $planData['id']]);
            } else {
                $plan->update(['mp_plan_id' => $planData['id']]);
            }

            // Guardar el collector_id en los metadatos del plan para validaciones futuras
            Log::info('MercadoPago Plan created successfully', [
                'plan_id' => $plan->id,
                'mp_plan_id' => $planData['id'],
                'collector_id' => $planData['collector_id'] ?? null,
                'application_id' => $planData['application_id'] ?? null,
                'init_point' => $planData['init_point'] ?? null,
            ]);

            return $planData;
        } catch (Exception $e) {
            Log::error('MercadoPago Preapproval Plan Error', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear un plan de preapproval específico para un cupón
     */
    public function createCouponPlan(SubscriptionPlan $plan, $coupon, string $billingCycle = 'monthly'): array
    {
        try {
            $basePrice = $billingCycle === 'annual' ? $plan->price_annual : $plan->price;
            $discountedPrice = $basePrice - $coupon->calculateDiscount($basePrice);
            $discountedPrice = max(0, $discountedPrice); // Ensure not negative

            // Obtener URL pública
            $configUrl = config('app.url');
            $publicUrl = env('MERCADOPAGO_PUBLIC_URL', $configUrl);

            if (empty($publicUrl) || str_contains($publicUrl, 'localhost') || str_contains($publicUrl, '127.0.0.1')) {
                $publicUrl = 'https://0f0101abbe45.ngrok-free.app';
            }

            $baseUrl = rtrim($publicUrl, '/');
            $backUrl = $baseUrl . '/subscriptions/success';

            $frequency = $billingCycle === 'annual' ? 12 : 1;
            $reason = "Cartify Premium - {$plan->name} ({$billingCycle}) - Coupon {$coupon->code}";

            $preapprovalPlanData = [
                'reason' => $reason,
                'auto_recurring' => [
                    'frequency' => $frequency,
                    'frequency_type' => 'months',
                    'repetitions' => null,
                    'billing_day_proportional' => false,
                    'transaction_amount' => (float) $discountedPrice,
                    'currency_id' => 'UYU',
                ],
                'payment_methods_allowed' => [
                    'payment_types' => [
                        ['id' => 'credit_card'],
                        ['id' => 'debit_card'],
                        ['id' => 'account_money'],
                    ],
                    'payment_methods' => [],
                ],
                'back_url' => $backUrl,
            ];

            Log::info('Creating Coupon MP Plan', ['payload' => $preapprovalPlanData]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'cplan_' . $plan->id . '_' . $coupon->id . '_' . $billingCycle . '_' . time(),
            ])->post("{$this->baseUrl}/preapproval_plan", $preapprovalPlanData);

            if (!$response->successful()) {
                throw new Exception('Error creating coupon plan: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('MercadoPago Coupon Plan Error', [
                'plan_id' => $plan->id,
                'coupon_code' => $coupon->code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener un plan de preapproval existente
     */
    public function getPreapprovalPlan(string $planId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/preapproval_plan/{$planId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('MercadoPago Get Preapproval Plan Error', [
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Crear una suscripción (preapproval) usando un plan y un token de tarjeta
     */
    public function createSubscriptionWithCardToken(Company $company, SubscriptionPlan $plan, string $cardTokenId): array
    {
        try {
            // Primero crear/obtener el plan de preapproval
            $mpPlan = $this->createOrGetPreapprovalPlan($plan);

            $user = $company->users()->where('is_owner', true)->first() ?? Auth::user();
            $payerEmail = $user->email ?? null;

            if (!$payerEmail) {
                throw new Exception('No se encontró email del pagador');
            }

            // Obtener URL pública (ngrok en desarrollo, APP_URL en producción)
            $configUrl = config('app.url');
            $publicUrl = env('MERCADOPAGO_PUBLIC_URL', $configUrl);

            // Si la URL configurada es localhost, usar la URL de ngrok por defecto
            if (empty($publicUrl) ||
                str_contains($publicUrl, 'localhost') ||
                str_contains($publicUrl, '127.0.0.1') ||
                str_starts_with($publicUrl, 'http://localhost') ||
                str_starts_with($publicUrl, 'http://127.0.0.1')) {
                $publicUrl = 'https://0f0101abbe45.ngrok-free.app';
            }

            $baseUrl = rtrim($publicUrl, '/');

            // Calcular la fecha de inicio: un mes después de hoy en GMT-3
            $startDate = \Carbon\Carbon::now('America/Montevideo') // GMT-3 (Uruguay)
                ->addMonth()
                ->startOfDay()
                ->toIso8601String();

            // Crear la suscripción (preapproval) con el plan y el token de tarjeta
            $preapprovalData = [
                'preapproval_plan_id' => $mpPlan['id'],
                'reason' => "Cartify Premium - {$plan->name}",
                'external_reference' => "subscription_{$company->id}_{$plan->id}",
                'payer_email' => $payerEmail,
                'card_token_id' => $cardTokenId,
                'status' => 'authorized',
                'auto_recurring' => [
                    'frequency' => 1,
                    'frequency_type' => 'months',
                    'start_date' => $startDate,
                    'transaction_amount' => (float) $plan->price,
                    'currency_id' => $company->currency ?? 'UYU',
                ],
                'back_url' => $baseUrl . '/subscriptions/success',
                'notification_url' => $baseUrl . '/api/webhooks/mercadopago',
                'metadata' => [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'type' => 'subscription',
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'sub_' . $company->id . '_' . $plan->id . '_' . time(),
            ])->post("{$this->baseUrl}/preapproval", $preapprovalData);

            if (!$response->successful()) {
                throw new Exception('Error creating subscription (preapproval): ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('MercadoPago Create Subscription Error', [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear un preapproval para cobros recurrentes mensuales
     *
     * Para suscripciones, creamos el preapproval después del primer pago aprobado
     * usando los datos del payment_method del pago inicial
     */
    public function createPreapproval(Company $company, SubscriptionPlan $plan, array $paymentData): array
    {
        try {
            $paymentMethodId = $paymentData['payment_method_id'] ?? null;
            $paymentTypeId = $paymentData['payment_type_id'] ?? null;

            // Solo crear preapproval si es pago con tarjeta
            if ($paymentTypeId !== 'credit_card' && $paymentTypeId !== 'debit_card') {
                throw new Exception('Preapprovals solo están disponibles para pagos con tarjeta');
            }

            // Obtener URL pública (ngrok en desarrollo, APP_URL en producción)
            $configUrl = config('app.url');
            $publicUrl = env('MERCADOPAGO_PUBLIC_URL', $configUrl);

            // Si la URL configurada es localhost, usar la URL de ngrok por defecto
            if (empty($publicUrl) ||
                str_contains($publicUrl, 'localhost') ||
                str_contains($publicUrl, '127.0.0.1') ||
                str_starts_with($publicUrl, 'http://localhost') ||
                str_starts_with($publicUrl, 'http://127.0.0.1')) {
                $publicUrl = 'https://0f0101abbe45.ngrok-free.app';
            }

            $baseUrl = rtrim($publicUrl, '/');

            // Calcular la fecha de inicio: un mes después de hoy en GMT-3
            $startDate = \Carbon\Carbon::now('America/Montevideo') // GMT-3 (Uruguay)
                ->addMonth()
                ->startOfDay()
                ->toIso8601String();

            // Construir datos del preapproval
            $preapprovalData = [
                'reason' => "Cartify Premium - {$plan->name}",
                'auto_recurring' => [
                    'frequency' => 1,
                    'frequency_type' => 'months',
                    'transaction_amount' => (float) $plan->price,
                    'currency_id' => $company->currency ?? 'UYU',
                    'start_date' => $startDate,
                    'end_date' => null, // Sin fecha de fin (suscripción activa hasta cancelación)
                ],
                'payment_method_id' => $paymentMethodId,
                'external_reference' => "preapproval_{$company->id}_{$plan->id}",
                'status' => 'authorized',
                'payer_email' => $company->users()->where('is_owner', true)->first()?->email ?? Auth::user()->email ?? $paymentData['payer']['email'] ?? null,
                'notification_url' => $baseUrl . '/api/webhooks/mercadopago',
                'metadata' => [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'subscription_type' => 'monthly',
                    'initial_payment_id' => $paymentData['id'] ?? null,
                ],
            ];

            // Si el pago tiene información de tarjeta guardada, usarla
            if (isset($paymentData['card']) && isset($paymentData['card']['id'])) {
                $preapprovalData['card_token_id'] = $paymentData['card']['id'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'preapproval_' . $company->id . '_' . $plan->id . '_' . time(),
            ])->post("{$this->baseUrl}/v1/preapproval", $preapprovalData);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Error creating preapproval: ' . $response->body());
        } catch (Exception $e) {
            Log::error('MercadoPago Preapproval Error', [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'payment_data' => $paymentData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener información de un pago
     */
    public function getPayment(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/v1/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Error getting payment: ' . $response->body());
        } catch (Exception $e) {
            Log::error('MercadoPago Get Payment Error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener información de un preapproval
     */
    public function getPreapproval(string $preapprovalId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/preapproval/{$preapprovalId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Error getting preapproval: ' . $response->body());
        } catch (Exception $e) {
            Log::error('MercadoPago Get Preapproval Error', [
                'preapproval_id' => $preapprovalId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancelar un preapproval (cancelar suscripción)
     */
    public function cancelPreapproval(string $preapprovalId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/preapproval/{$preapprovalId}", [
                'status' => 'cancelled',
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('MercadoPago Cancel Preapproval Error', [
                'preapproval_id' => $preapprovalId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Procesar webhook recibido de MercadoPago
     */
    public function processWebhook(array $data): void
    {
        try {
            $type = $data['type'] ?? null;
            $action = $data['action'] ?? null;

            Log::info('MercadoPago Webhook Received', [
                'type' => $type,
                'action' => $action,
                'data' => $data,
            ]);

            // Procesar según el tipo de webhook
            switch ($type) {
                case 'payment':
                    $this->processPaymentWebhook($data);
                    break;
                case 'subscription_preapproval':
                    $this->processPreapprovalWebhook($data);
                    break;
                case 'subscription_authorized_payment':
                    $this->processAuthorizedPaymentWebhook($data);
                    break;
                default:
                    Log::info('MercadoPago webhook type received (not processed)', [
                        'type' => $type,
                        'action' => $action,
                    ]);
                    // No lanzamos warning para tipos conocidos pero no procesados
                    // Simplemente registramos que se recibió y continuamos
            }
        } catch (Exception $e) {
            Log::error('MercadoPago Webhook Processing Error', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // No lanzar la excepción - el controller ya retorna 200 OK en caso de error
            // Esto previene que MercadoPago reenvíe el webhook indefinidamente
        }
    }

    /**
     * Procesar webhook de pago
     */
    private function processPaymentWebhook(array $data): void
    {
        $paymentId = $data['data']['id'] ?? null;
        $action = $data['action'] ?? null;

        if (!$paymentId) {
            Log::warning('Payment webhook without payment ID', ['data' => $data]);
            return;
        }

        // Obtener información completa del pago
        $payment = $this->getPayment($paymentId);

        Log::info('Processing Payment Webhook', [
            'payment_id' => $paymentId,
            'action' => $action,
            'payment_status' => $payment['status'] ?? null,
            'external_reference' => $payment['external_reference'] ?? null,
            'metadata' => $payment['metadata'] ?? null,
        ]);

        // Extraer metadata para identificar la suscripción
        $metadata = $payment['metadata'] ?? [];
        $externalReference = $payment['external_reference'] ?? '';
        $status = $payment['status'] ?? null;

        // Buscar suscripción por preapproval_id si el pago viene de una suscripción
        $subscription = null;
        $preapprovalId = null;

        // Verificar si el pago está relacionado con un preapproval
        if (isset($payment['subscription_id'])) {
            $preapprovalId = $payment['subscription_id'];
            $subscription = Subscription::where('mp_preapproval_id', $preapprovalId)->first();
        }

        // Si no encontramos por subscription_id, intentar por external_reference
        if (!$subscription && str_starts_with($externalReference, 'subscription_')) {
            // Parsear external_reference: subscription_{company_id}_{plan_id}
            $parts = explode('_', $externalReference);
            if (count($parts) >= 3) {
                $companyId = (int) $parts[1];
                $planId = (int) $parts[2];

                $company = Company::find($companyId);
                $plan = SubscriptionPlan::find($planId);

                if ($company && $plan) {
                    $subscription = $company->activeSubscription;
                    if (!$subscription || $subscription->plan_id != $planId) {
                        // No hay suscripción activa o es de un plan diferente
                        // Esto se manejará en handlePaymentApproved
                        $this->processPaymentByExternalReference($company, $plan, $payment);
                        return;
                    }
                }
            }
        } elseif ($subscription) {
            // Ya tenemos la suscripción, registrar el pago
            $this->recordSubscriptionPayment($subscription, $payment);

            if ($status === 'approved') {
                $subscription->renew();
                Log::info('Subscription payment approved and renewed', [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $paymentId,
                ]);
            }
        } else {
            // No es un pago de suscripción o no encontramos la suscripción relacionada
            Log::info('Payment webhook ignored - not a subscription payment or subscription not found', [
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
                'has_subscription_id' => isset($payment['subscription_id']),
            ]);
        }
    }

    /**
     * Procesar pago por external_reference (flujo legacy)
     */
    private function processPaymentByExternalReference(Company $company, SubscriptionPlan $plan, array $payment): void
    {
        $status = $payment['status'] ?? null;

        // Procesar según el estado del pago
        switch ($status) {
            case 'approved':
                $this->handlePaymentApproved($company, $plan, $payment);
                break;
            case 'rejected':
            case 'cancelled':
                $this->handlePaymentRejected($company, $plan, $payment);
                break;
            default:
                Log::info('Payment status not processed', [
                    'status' => $status,
                    'payment_id' => $payment['id'] ?? null,
                ]);
        }
    }

    /**
     * Manejar pago aprobado
     */
    private function handlePaymentApproved(Company $company, SubscriptionPlan $plan, array $payment): void
    {
        try {
            // Verificar si ya existe una suscripción activa
            $existingSubscription = $company->activeSubscription;

            if ($existingSubscription && $existingSubscription->isActive()) {
                // Es un pago recurrente, registrar el pago
                $this->recordSubscriptionPayment($existingSubscription, $payment);
                $existingSubscription->renew();
            } else {
                // Es el pago inicial, crear suscripción y preapproval
                $this->createSubscriptionFromPayment($company, $plan, $payment);
            }
        } catch (Exception $e) {
            Log::error('Error handling approved payment', [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment['id'],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear suscripción desde pago aprobado
     */
    private function createSubscriptionFromPayment(Company $company, SubscriptionPlan $plan, array $payment): void
    {
        try {
            // Crear preapproval para cobros recurrentes usando los datos del pago
            $preapproval = $this->createPreapproval($company, $plan, $payment);

            // Crear registro de suscripción
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'mp_subscription_id' => $preapproval['id'] ?? null,
                'mp_preapproval_id' => $preapproval['id'] ?? null,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            // Registrar el pago inicial
            $this->recordSubscriptionPayment($subscription, $payment);

            // Actualizar company
            $company->update([
                'plan_id' => $plan->id,
                'subscription_id' => $subscription->id,
            ]);

            Log::info('Subscription created from payment', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment['id'],
            ]);

            // TODO: Enviar email de confirmación
        } catch (Exception $e) {
            Log::error('Error creating subscription from payment', [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment['id'],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Registrar pago de suscripción
     */
    private function recordSubscriptionPayment(Subscription $subscription, array $payment): void
    {
        $subscription->payments()->create([
            'mp_payment_id' => $payment['id'],
            'amount' => $payment['transaction_amount'] ?? 0,
            'currency' => $payment['currency_id'] ?? 'UYU',
            'status' => $this->mapPaymentStatus($payment['status'] ?? 'pending'),
            'payment_date' => now(),
            'metadata' => $payment,
        ]);
    }

    /**
     * Mapear status de MercadoPago a nuestro enum
     */
    private function mapPaymentStatus(string $mpStatus): string
    {
        return match($mpStatus) {
            'approved' => 'approved',
            'rejected', 'cancelled' => 'rejected',
            'pending', 'in_process', 'in_mediation' => 'pending',
            default => 'pending',
        };
    }

    /**
     * Manejar pago rechazado
     */
    private function handlePaymentRejected(Company $company, SubscriptionPlan $plan, array $payment): void
    {
        $subscription = $company->activeSubscription;

        if ($subscription) {
            $subscription->status = 'past_due';
            $subscription->save();

            // Registrar el intento fallido
            $this->recordSubscriptionPayment($subscription, $payment);

            Log::info('Subscription marked as past_due', [
                'subscription_id' => $subscription->id,
                'payment_id' => $payment['id'],
            ]);

            // TODO: Enviar email de alerta
        }
    }

    /**
     * Procesar webhook de preapproval
     */
    private function processPreapprovalWebhook(array $data): void
    {
        $preapprovalId = $data['data']['id'] ?? null;
        $action = $data['action'] ?? null;

        if (!$preapprovalId) {
            Log::warning('Preapproval webhook without preapproval ID', ['data' => $data]);
            return;
        }

        try {
            $preapproval = $this->getPreapproval($preapprovalId);

            if (!$preapproval) {
                Log::warning('Could not fetch preapproval data', ['preapproval_id' => $preapprovalId]);
                return;
            }

            $status = $preapproval['status'] ?? null;
            $preapprovalPlanId = $preapproval['preapproval_plan_id'] ?? null;
            $externalReference = $preapproval['external_reference'] ?? '';
            $payerEmail = $preapproval['payer_email'] ?? null;

            Log::info('Processing Preapproval Webhook', [
                'preapproval_id' => $preapprovalId,
                'action' => $action,
                'status' => $status,
                'preapproval_plan_id' => $preapprovalPlanId,
                'external_reference' => $externalReference,
                'payer_email' => $payerEmail,
            ]);

            // Buscar si ya existe una suscripción con este preapproval_id
            $subscription = Subscription::where('mp_preapproval_id', $preapprovalId)->first();

            if ($subscription) {
                // Ya existe, solo actualizar el estado
                Log::info('Subscription already exists, updating status', [
                    'subscription_id' => $subscription->id,
                    'preapproval_id' => $preapprovalId,
                    'status' => $status,
                ]);

                switch ($status) {
                    case 'authorized':
                    case 'active':
                        $subscription->status = 'active';
                        if ($action === 'created' && !$subscription->current_period_start) {
                            $subscription->current_period_start = now();
                            $subscription->current_period_end = now()->addMonth();
                        }
                        break;
                    case 'cancelled':
                        $subscription->cancel();
                        break;
                    case 'paused':
                        $subscription->status = 'paused';
                        break;
                }
                $subscription->save();
                return;
            }

            // Si no existe la suscripción y el status es "authorized" o "active", crear una nueva
            // Esto puede pasar tanto en "created" como en "updated" (cuando cambia de pending a authorized)
            if (($action === 'created' || $action === 'updated') && ($status === 'authorized' || $status === 'active')) {
                // Buscar el plan usando el preapproval_plan_id
                $plan = SubscriptionPlan::where('mp_plan_id', $preapprovalPlanId)->first();

                if (!$plan) {
                    Log::warning('Plan not found for preapproval_plan_id', [
                        'preapproval_plan_id' => $preapprovalPlanId,
                        'preapproval_id' => $preapprovalId,
                    ]);
                    return;
                }

                // Buscar la company usando múltiples métodos
                $company = null;

                // Método 1: Buscar en cache usando mp_plan_id
                // El cache key se guarda como: subscription_intent_{mp_plan_id}_{company_id}
                // Buscar en las últimas 50 companies actualizadas (que probablemente están intentando suscribirse)
                $cachedIntent = null;
                $usedCacheKey = null;

                // Buscar por pattern: subscription_intent_{mp_plan_id}_{company_id}
                $recentCompanies = Company::orderBy('updated_at', 'desc')->limit(100)->pluck('id');
                foreach ($recentCompanies as $companyId) {
                    $cacheKey = "subscription_intent_{$preapprovalPlanId}_{$companyId}";
                    $intent = cache()->get($cacheKey);
                    if ($intent && isset($intent['mp_plan_id']) && $intent['mp_plan_id'] === $preapprovalPlanId) {
                        $cachedIntent = $intent;
                        $usedCacheKey = $cacheKey;
                        Log::info('Found cached intent with correct format', [
                            'cache_key' => $cacheKey,
                            'company_id' => $cachedIntent['company_id'] ?? null,
                        ]);
                        break;
                    }
                }

                // También buscar por formatos alternativos por si acaso
                if (!$cachedIntent) {
                    $possibleCacheKeys = [
                        "subscription_intent_{$preapprovalPlanId}_{$plan->id}",
                        "subscription_intent_mp_plan_{$preapprovalPlanId}",
                    ];

                    foreach ($possibleCacheKeys as $cacheKey) {
                        $intent = cache()->get($cacheKey);
                        if ($intent && isset($intent['mp_plan_id']) && $intent['mp_plan_id'] === $preapprovalPlanId) {
                            $cachedIntent = $intent;
                            $usedCacheKey = $cacheKey;
                            break;
                        }
                    }
                }

                if ($cachedIntent && isset($cachedIntent['company_id'])) {
                    $company = Company::find($cachedIntent['company_id']);
                    if ($company) {
                        Log::info('Found company from cache', [
                            'company_id' => $company->id,
                            'cache_key' => $usedCacheKey,
                            'mp_plan_id' => $preapprovalPlanId,
                        ]);
                        // Limpiar el cache después de usarlo
                        if ($usedCacheKey) {
                            cache()->forget($usedCacheKey);
                        }
                    }
                }

                // Método 2: Intentar parsear external_reference: subscription_{company_id}_{plan_id}
                if (!$company && str_starts_with($externalReference, 'subscription_')) {
                    $parts = explode('_', $externalReference);
                    if (count($parts) >= 3) {
                        $companyId = (int) $parts[1];
                        $company = Company::find($companyId);
                        if ($company) {
                            Log::info('Found company from external_reference', [
                                'company_id' => $company->id,
                                'external_reference' => $externalReference,
                            ]);
                        }
                    }
                }

                // Método 3: Buscar por payer_email
                if (!$company && $payerEmail) {
                    $user = \App\Models\User::where('email', $payerEmail)->first();
                    if ($user && $user->company) {
                        $company = $user->company;
                        Log::info('Found company from payer_email', [
                            'company_id' => $company->id,
                            'payer_email' => $payerEmail,
                        ]);
                    }
                }

                // Método 4: Buscar por plan_id y usuario que no tenga suscripción premium activa
                // Esto es un último recurso - buscar companies sin suscripción premium que podrían estar intentando suscribirse
                if (!$company) {
                    // Buscar la company más reciente sin suscripción premium que coincida con el plan
                    $potentialCompany = Company::where('plan_id', '!=', $plan->id)
                        ->orWhereNull('plan_id')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($potentialCompany) {
                        Log::info('Using potential company as fallback', [
                            'company_id' => $potentialCompany->id,
                            'plan_id' => $plan->id,
                        ]);
                        $company = $potentialCompany;
                    }
                }

                if (!$company) {
                    Log::warning('Could not find company for preapproval - subscription will not be created automatically', [
                        'preapproval_id' => $preapprovalId,
                        'external_reference' => $externalReference,
                        'payer_email' => $payerEmail,
                        'preapproval_plan_id' => $preapprovalPlanId,
                        'plan_id' => $plan->id,
                        'action' => $action,
                        'status' => $status,
                    ]);
                    // No crear la suscripción aquí - el callback de success lo manejará cuando el usuario vuelva
                    return;
                }

                // Crear la suscripción local
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
                if ($plan->slug !== 'free') {
                    $this->unblockAllResources($company);
                }

                Log::info('Subscription created from preapproval webhook', [
                    'subscription_id' => $subscription->id,
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'preapproval_id' => $preapprovalId,
                ]);

                // Enviar email de suscripción comprada (solo para plan premium)
                if ($plan->slug !== 'free') {
                    $subscription->load('plan');
                    app(\App\Services\EmailService::class)->sendSubscriptionPurchasedEmail($company, $subscription);
                }

                // Si hay un pago asociado (del webhook de payment), registrarlo
                // Esto se manejará en processPaymentWebhook
            } elseif ($action === 'updated' && $subscription) {
                // Si la suscripción existe, actualizar su estado
                switch ($status) {
                    case 'cancelled':
                        $subscription->cancel();
                        break;
                    case 'paused':
                        $subscription->status = 'paused';
                        break;
                    case 'authorized':
                    case 'active':
                        $subscription->status = 'active';
                        // Actualizar company si es necesario
                        if ($subscription->company && $subscription->company->plan_id != $subscription->plan_id) {
                            $subscription->company->update([
                                'plan_id' => $subscription->plan_id,
                                'subscription_id' => $subscription->id,
                            ]);
                        }
                        break;
                }
                $subscription->save();
            }
        } catch (Exception $e) {
            Log::error('Error processing preapproval webhook', [
                'preapproval_id' => $preapprovalId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Procesar webhook de pago autorizado de suscripción (pago recurrente mensual)
     */
    private function processAuthorizedPaymentWebhook(array $data): void
    {
        $authorizedPaymentId = $data['data']['id'] ?? null;
        $action = $data['action'] ?? null;

        if (!$authorizedPaymentId) {
            Log::warning('Authorized payment webhook without ID', ['data' => $data]);
            return;
        }

        try {
            // Obtener información del pago autorizado desde MercadoPago
            $authorizedPayment = $this->getAuthorizedPayment($authorizedPaymentId);

            if (!$authorizedPayment) {
                Log::warning('Could not fetch authorized payment data', [
                    'authorized_payment_id' => $authorizedPaymentId,
                ]);
                return;
            }

            $preapprovalId = $authorizedPayment['preapproval_id'] ?? null;
            $status = $authorizedPayment['status'] ?? null;

            Log::info('Processing Authorized Payment Webhook', [
                'authorized_payment_id' => $authorizedPaymentId,
                'action' => $action,
                'status' => $status,
                'preapproval_id' => $preapprovalId,
            ]);

            // Buscar la suscripción usando el preapproval_id
            if (!$preapprovalId) {
                Log::warning('Authorized payment without preapproval_id', [
                    'authorized_payment_id' => $authorizedPaymentId,
                ]);
                return;
            }

            $subscription = Subscription::where('mp_preapproval_id', $preapprovalId)->first();

            if (!$subscription) {
                Log::warning('Subscription not found for authorized payment', [
                    'authorized_payment_id' => $authorizedPaymentId,
                    'preapproval_id' => $preapprovalId,
                ]);
                return;
            }

            // Procesar según el estado del pago autorizado
            switch ($status) {
                case 'authorized':
                case 'approved':
                    // Pago aprobado - registrar el pago y renovar la suscripción
                    $this->recordAuthorizedPayment($subscription, $authorizedPayment);

                    // Salir del período de gracia si estaba en uno
                    if ($subscription->in_grace_period) {
                        $subscription->exitGracePeriod();
                    }

                    // Para pagos recurrentes mensuales, renovar si el período está vencido o cerca de vencer
                    // También renovar si estaba en período de gracia
                    $shouldRenew = false;

                    if ($subscription->in_grace_period) {
                        // Si estaba en período de gracia, siempre renovar
                        $shouldRenew = true;
                    } elseif ($subscription->current_period_end->isPast()) {
                        // Si el período ya venció, renovar
                        $shouldRenew = true;
                    } elseif ($subscription->current_period_end->isToday()) {
                        // Si vence hoy, renovar
                        $shouldRenew = true;
                    } elseif (now()->diffInDays($subscription->current_period_end, false) <= 2) {
                        // Si vence en 2 días o menos, renovar (para mantener la suscripción activa continuamente)
                        // diffInDays con false retorna días absolutos sin considerar si es pasado o futuro
                        $shouldRenew = true;
                    }

                    if ($shouldRenew) {
                        $subscription->renew();

                        // Desbloquear todos los recursos si estaban bloqueados
                        $this->unblockAllResources($subscription->company);

                        Log::info('Subscription renewed from authorized payment', [
                            'subscription_id' => $subscription->id,
                            'authorized_payment_id' => $authorizedPaymentId,
                            'new_period_end' => $subscription->current_period_end,
                            'was_in_grace_period' => $subscription->in_grace_period ?? false,
                        ]);
                    } else {
                        // El período aún no está cerca de vencer, solo registrar el pago y asegurar que recursos estén desbloqueados
                        $this->unblockAllResources($subscription->company);

                        Log::info('Authorized payment recorded, subscription period not expired yet', [
                            'subscription_id' => $subscription->id,
                            'authorized_payment_id' => $authorizedPaymentId,
                            'current_period_end' => $subscription->current_period_end,
                            'days_until_expiry' => now()->diffInDays($subscription->current_period_end),
                        ]);
                    }
                    break;

                case 'rejected':
                case 'cancelled':
                case 'failed':
                    // Pago rechazado/fallido - entrar en período de gracia o marcar como past_due
                    if ($subscription->current_period_end->isPast()) {
                        // El período ya venció, entrar en período de gracia si no está ya en uno
                        if (!$subscription->in_grace_period) {
                            $subscription->enterGracePeriod();

                            Log::warning('Subscription entered grace period due to failed authorized payment', [
                                'subscription_id' => $subscription->id,
                                'authorized_payment_id' => $authorizedPaymentId,
                                'status' => $status,
                                'grace_period_ends_at' => $subscription->grace_period_ends_at,
                            ]);

                            // Enviar email de pago fallido
                            $subscription->load('plan');
                            app(\App\Services\EmailService::class)->sendSubscriptionPaymentFailedEmail($subscription->company, $subscription);
                        } else {
                            // Ya está en período de gracia, solo registrar el intento fallido
                            $subscription->last_payment_failed_at = now();
                            $subscription->save();
                        }
                    } else {
                        // El período aún no venció, solo marcar como past_due
                        $subscription->status = 'past_due';
                        $subscription->last_payment_failed_at = now();
                        $subscription->save();
                    }

                    $this->recordAuthorizedPayment($subscription, $authorizedPayment);

                    Log::warning('Subscription payment failed', [
                        'subscription_id' => $subscription->id,
                        'authorized_payment_id' => $authorizedPaymentId,
                        'status' => $status,
                        'in_grace_period' => $subscription->in_grace_period,
                    ]);
                    break;

                default:
                    Log::info('Authorized payment status not processed', [
                        'status' => $status,
                        'authorized_payment_id' => $authorizedPaymentId,
                    ]);
            }
        } catch (Exception $e) {
            Log::error('Error processing authorized payment webhook', [
                'authorized_payment_id' => $authorizedPaymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Obtener información de un pago autorizado desde MercadoPago
     * El endpoint correcto es /v1/authorized_payments/{id} según la documentación
     */
    private function getAuthorizedPayment(string $authorizedPaymentId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/v1/authorized_payments/{$authorizedPaymentId}");

            if (!$response->successful()) {
                Log::error('MercadoPago Get Authorized Payment Error', [
                    'authorized_payment_id' => $authorizedPaymentId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('MercadoPago Get Authorized Payment Exception', [
                'authorized_payment_id' => $authorizedPaymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Registrar un pago autorizado en la suscripción
     */
    private function recordAuthorizedPayment(Subscription $subscription, array $authorizedPayment): void
    {
        $paymentData = [
            'id' => $authorizedPayment['id'] ?? null,
            'transaction_amount' => $authorizedPayment['transaction_amount'] ?? 0,
            'currency_id' => $authorizedPayment['currency_id'] ?? 'UYU',
            'status' => $authorizedPayment['status'] ?? 'pending',
        ];

        $subscription->payments()->create([
            'mp_payment_id' => $authorizedPayment['id'] ?? null,
            'amount' => $paymentData['transaction_amount'],
            'currency' => $paymentData['currency_id'],
            'status' => $this->mapPaymentStatus($paymentData['status']),
            'payment_date' => now(),
            'metadata' => $authorizedPayment,
        ]);

        Log::info('Authorized payment recorded', [
            'subscription_id' => $subscription->id,
            'authorized_payment_id' => $authorizedPayment['id'] ?? null,
            'amount' => $paymentData['transaction_amount'],
        ]);
    }

    /**
     * Desbloquear todos los recursos de una company (cuando se renueva o paga la suscripción).
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

        Log::info('All resources unblocked after subscription payment', [
            'company_id' => $company->id,
        ]);
    }

    /**
     * Validar firma del webhook (si está configurada)
     */
    public function validateWebhookSignature(array $data, string $signature): bool
    {
        // Si no hay secret configurado, no validar (solo para desarrollo)
        $webhookSecret = config('services.mercadopago.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('Webhook secret not configured, skipping signature validation');
            return true; // En desarrollo puede estar desactivado
        }

        // Implementar validación de firma según documentación de MP
        // Por ahora retornamos true si no hay secret configurado
        return true;
    }

    /**
     * Obtener collector_id del ACCESS_TOKEN actual
     * El collector_id se puede obtener de la respuesta de cualquier endpoint que lo devuelva
     * o haciendo una petición GET a /users/me si está disponible
     */
    private function getCollectorIdFromToken(): ?int
    {
        try {
            // Intentar obtener el collector_id desde una petición de prueba
            // Si no funciona, retornar null y se validará después
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/users/me");

            if ($response->successful()) {
                $userData = $response->json();
                Log::info('Collector ID from token', [
                    'collector_id' => $userData['id'] ?? null,
                    'user_data' => $userData,
                ]);
                return $userData['id'] ?? null;
            }

            // Si /users/me no funciona, intentar obtenerlo de otro endpoint
            // Por ahora retornamos null y validaremos comparando el collector_id del plan
            Log::warning('Could not get collector_id from /users/me', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        } catch (Exception $e) {
            Log::warning('Error getting collector_id from token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener Public Key para usar en frontend
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Obtener App ID
     */
    public function getAppId(): string
    {
        return $this->appId;
    }
}

