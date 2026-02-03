<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Company;
use App\Models\Restaurant;
use App\Models\QrCode;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscription expiry and handle grace periods, downgrade to free plan if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking subscription expiry...');

        // Buscar suscripciones que vencieron pero aún están activas (necesitan verificación)
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('current_period_end', '<', now())
            ->where(function ($query) {
                $query->whereNull('in_grace_period')
                      ->orWhere('in_grace_period', false);
            })
            ->with(['company', 'plan'])
            ->get();

        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions to check");

        foreach ($expiredSubscriptions as $subscription) {
            $this->handleExpiredSubscription($subscription);
        }

        // Buscar suscripciones en período de gracia que ya vencieron
        $gracePeriodExpired = Subscription::where('status', 'past_due')
            ->where('in_grace_period', true)
            ->where('grace_period_ends_at', '<', now())
            ->with(['company', 'plan'])
            ->get();

        $this->info("Found {$gracePeriodExpired->count()} subscriptions with expired grace period");

        foreach ($gracePeriodExpired as $subscription) {
            $this->handleGracePeriodExpired($subscription);
        }

        $this->info('Subscription expiry check completed!');

        return Command::SUCCESS;
    }

    /**
     * Handle expired subscription - check if payment was received, otherwise enter grace period.
     */
    private function handleExpiredSubscription(Subscription $subscription): void
    {
        $company = $subscription->company;

        if (!$company) {
            Log::warning('Subscription without company', ['subscription_id' => $subscription->id]);
            return;
        }

        // Verificar si hay un pago reciente después de que venció el período
        // Buscar pagos desde que venció el período hasta ahora (período de gracia de 3 días)
        $periodEndDate = $subscription->current_period_end;
        $recentPayment = $subscription->payments()
            ->where('created_at', '>=', $periodEndDate)
            ->where('created_at', '<=', now())
            ->where('status', 'approved')
            ->latest()
            ->first();

        if ($recentPayment) {
            // Hay un pago reciente, renovar la suscripción
            $subscription->renew();
            $subscription->exitGracePeriod();

            Log::info('Subscription renewed after finding recent payment', [
                'subscription_id' => $subscription->id,
                'company_id' => $company->id,
                'payment_id' => $recentPayment->id,
            ]);

            $this->info("Renewed subscription {$subscription->id} for company {$company->id}");
            return;
        }

        // No hay pago reciente, entrar en período de gracia
        if (!$subscription->in_grace_period) {
            $subscription->enterGracePeriod();

            Log::info('Subscription entered grace period', [
                'subscription_id' => $subscription->id,
                'company_id' => $company->id,
                'grace_period_ends_at' => $subscription->grace_period_ends_at,
            ]);

            $this->info("Subscription {$subscription->id} entered grace period until {$subscription->grace_period_ends_at}");

            // Enviar email de pago fallido
            $subscription->load('plan');
            app(EmailService::class)->sendSubscriptionPaymentFailedEmail($company, $subscription);
        }
    }

    /**
     * Handle expired grace period - downgrade to free plan and block resources.
     */
    private function handleGracePeriodExpired(Subscription $subscription): void
    {
        $company = $subscription->company;

        if (!$company) {
            Log::warning('Subscription without company in grace period expired', [
                'subscription_id' => $subscription->id,
            ]);
            return;
        }

        $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();

        if (!$freePlan) {
            Log::error('Free plan not found, cannot downgrade subscription', [
                'subscription_id' => $subscription->id,
                'company_id' => $company->id,
            ]);
            return;
        }

        // Cancelar la suscripción premium
        $subscription->status = 'expired';
        $subscription->ends_at = now();
        $subscription->exitGracePeriod();
        $subscription->save();

        // Asignar plan free
        $company->update([
            'plan_id' => $freePlan->id,
            'subscription_id' => null,
        ]);

        // Bloquear recursos que exceden los límites del plan free
        $this->blockExcessResources($company, $freePlan);

        Log::info('Subscription downgraded to free plan after grace period expired', [
            'subscription_id' => $subscription->id,
            'company_id' => $company->id,
            'free_plan_id' => $freePlan->id,
        ]);

        $this->info("Downgraded subscription {$subscription->id} to free plan for company {$company->id}");

        // Enviar email de suscripción cancelada (por expiración del grace period)
        $subscription->load('plan');
        app(EmailService::class)->sendSubscriptionCancelledEmail($company, $subscription);
    }

    /**
     * Block resources that exceed free plan limits.
     */
    private function blockExcessResources(Company $company, $freePlan): void
    {
        $limits = $freePlan->getLimits();

        // Bloquear restaurants excedentes
        $restaurantLimit = $limits['restaurants'] ?? 1;
        $restaurants = $company->restaurants()->orderBy('created_at')->get();
        $activeCount = 0;

        foreach ($restaurants as $restaurant) {
            if ($activeCount < $restaurantLimit) {
                // Mantener activo
                if ($restaurant->is_blocked) {
                    $restaurant->unblock();
                }
                $activeCount++;
            } else {
                // Bloquear
                if (!$restaurant->is_blocked) {
                    $restaurant->block('subscription_downgrade');
                }
            }
        }

        // Bloquear usuarios excedentes (excepto owners)
        $userLimit = $limits['users'] ?? 1;
        $users = $company->users()->where('is_owner', false)->orderBy('created_at')->get();
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

        // Bloquear QR codes excedentes
        $qrCodeLimit = $limits['qr_codes'] ?? 2;
        $totalQrCodes = $company->getTotalQrCodesCount();
        $qrCodes = QrCode::whereIn('restaurant_id', $company->restaurants->pluck('id'))
            ->orderBy('created_at')
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

        Log::info('Excess resources blocked after subscription downgrade', [
            'company_id' => $company->id,
            'restaurants_blocked' => $restaurants->count() - $activeCount,
            'users_blocked' => $users->count() - $activeUserCount,
            'qr_codes_blocked' => $qrCodes->count() - $activeQrCount,
        ]);
    }
}
