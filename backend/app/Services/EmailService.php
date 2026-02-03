<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar email cuando suscripción se compra correctamente
     */
    public function sendSubscriptionPurchasedEmail(Company $company, Subscription $subscription): void
    {
        try {
            $webhookUrl = config('services.n8n.subscription_purchased_webhook');
            
            if (!$webhookUrl) {
                Log::warning('N8N subscription purchased webhook URL not configured');
                return;
            }

            $owner = $company->users()->where('is_owner', true)->first();
            $plan = $subscription->plan;
            
            Http::timeout(10)->post($webhookUrl, [
                // Datos del cliente/usuario
                'user_id' => $owner->id ?? null,
                'user_email' => $owner->email ?? $company->users()->first()->email,
                'user_name' => $owner->name ?? $company->users()->first()->name,
                
                // Datos de la compañía
                'company_id' => $company->id,
                'company_name' => $company->name,
                'company_currency' => $company->currency,
                
                // Datos de la suscripción
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start->toIso8601String(),
                'current_period_end' => $subscription->current_period_end->toIso8601String(),
                
                // Datos del plan
                'plan_id' => $plan->id ?? null,
                'plan_name' => $plan->name ?? 'Premium',
                'plan_slug' => $plan->slug ?? 'premium',
                'plan_description' => $plan->description ?? null,
                'plan_price' => $plan->price ?? null,
                'plan_limits' => [
                    'restaurants' => $plan->getLimit('restaurants'),
                    'users' => $plan->getLimit('users'),
                    'qr_codes' => $plan->getLimit('qr_codes'),
                ],
                'plan_features' => $plan->getFeatures() ?? [],
            ]);

            Log::info('Subscription purchased email sent via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending subscription purchased email via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar email cuando pago falla y entra en grace period (3 días para pagar)
     */
    public function sendSubscriptionPaymentFailedEmail(Company $company, Subscription $subscription): void
    {
        try {
            $webhookUrl = config('services.n8n.subscription_payment_failed_webhook');
            
            if (!$webhookUrl) {
                Log::warning('N8N subscription payment failed webhook URL not configured');
                return;
            }

            $owner = $company->users()->where('is_owner', true)->first();
            $gracePeriodEndsAt = $subscription->grace_period_ends_at;
            $daysRemaining = $gracePeriodEndsAt ? now()->diffInDays($gracePeriodEndsAt, false) : 3;
            
            Http::timeout(10)->post($webhookUrl, [
                'email' => $owner->email ?? $company->users()->first()->email,
                'name' => $owner->name ?? $company->users()->first()->name,
                'company_name' => $company->name,
                'plan_name' => $subscription->plan->name ?? 'Premium',
                'subscription_id' => $subscription->id,
                'grace_period_ends_at' => $gracePeriodEndsAt->toIso8601String(),
                'days_remaining' => max(0, $daysRemaining),
                'payment_url' => route('admin.subscription'),
            ]);

            Log::info('Subscription payment failed email sent via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'grace_period_ends_at' => $gracePeriodEndsAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending subscription payment failed email via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar email cuando suscripción se cancela
     */
    public function sendSubscriptionCancelledEmail(Company $company, Subscription $subscription): void
    {
        try {
            $webhookUrl = config('services.n8n.subscription_cancelled_webhook');
            
            if (!$webhookUrl) {
                Log::warning('N8N subscription cancelled webhook URL not configured');
                return;
            }

            $owner = $company->users()->where('is_owner', true)->first();
            $endsAt = $subscription->ends_at ?? $subscription->current_period_end;
            
            Http::timeout(10)->post($webhookUrl, [
                'email' => $owner->email ?? $company->users()->first()->email,
                'name' => $owner->name ?? $company->users()->first()->name,
                'company_name' => $company->name,
                'plan_name' => $subscription->plan->name ?? 'Premium',
                'subscription_id' => $subscription->id,
                'ends_at' => $endsAt->toIso8601String(),
                'reactivate_url' => route('admin.subscription'),
            ]);

            Log::info('Subscription cancelled email sent via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'ends_at' => $endsAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending subscription cancelled email via n8n', [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

