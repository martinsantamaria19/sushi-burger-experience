<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSubscriptionWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:subscription-webhooks {type=purchased : Tipo de webhook a testear (purchased|payment-failed|cancelled)} {--company-id= : ID de la compañía a usar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando temporal para testear los webhooks de suscripciones (purchased, payment-failed, cancelled)';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService)
    {
        $type = $this->argument('type');
        $companyId = $this->option('company-id');

        // Buscar o usar la primera compañía disponible
        if ($companyId) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("No se encontró la compañía con ID: {$companyId}");
                return Command::FAILURE;
            }
        } else {
            $company = Company::with('users')->first();
            if (!$company) {
                $this->error('No hay compañías disponibles en la base de datos.');
                return Command::FAILURE;
            }
        }

        $this->info("Usando compañía: {$company->name} (ID: {$company->id})");

        switch ($type) {
            case 'purchased':
                $this->testPurchasedWebhook($emailService, $company);
                break;
            case 'payment-failed':
                $this->testPaymentFailedWebhook($emailService, $company);
                break;
            case 'cancelled':
                $this->testCancelledWebhook($emailService, $company);
                break;
            default:
                $this->error("Tipo de webhook inválido: {$type}");
                $this->info("Tipos disponibles: purchased, payment-failed, cancelled");
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Test webhook de suscripción comprada
     */
    private function testPurchasedWebhook(EmailService $emailService, Company $company): void
    {
        $this->info('Testing webhook: subscription_purchased_webhook');

        // Buscar suscripción premium activa o crear una temporal
        $subscription = $company->activeSubscription;
        $premiumPlan = SubscriptionPlan::getPremiumPlan();

        if (!$subscription && $premiumPlan) {
            $this->warn('No hay suscripción activa. Creando una temporal para el test...');
            
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $premiumPlan->id,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            $this->info("Suscripción temporal creada (ID: {$subscription->id})");
        } elseif (!$subscription) {
            $this->error('No hay suscripción activa y no se pudo crear una temporal.');
            return;
        }

        $subscription->load('plan');
        $this->info("Enviando webhook para suscripción ID: {$subscription->id}");
        
        $emailService->sendSubscriptionPurchasedEmail($company, $subscription);
        
        $this->info('✅ Webhook enviado correctamente. Revisa los logs y tu webhook de n8n.');
    }

    /**
     * Test webhook de pago fallido
     */
    private function testPaymentFailedWebhook(EmailService $emailService, Company $company): void
    {
        $this->info('Testing webhook: subscription_payment_failed_webhook');

        // Buscar suscripción premium activa o crear una temporal
        $subscription = $company->activeSubscription;
        $premiumPlan = SubscriptionPlan::getPremiumPlan();

        if (!$subscription && $premiumPlan) {
            $this->warn('No hay suscripción activa. Creando una temporal para el test...');
            
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $premiumPlan->id,
                'status' => 'past_due',
                'current_period_start' => now()->subMonth(),
                'current_period_end' => now()->subDay(),
                'in_grace_period' => true,
                'grace_period_ends_at' => now()->addDays(3),
            ]);

            $this->info("Suscripción temporal creada (ID: {$subscription->id})");
        } elseif (!$subscription) {
            $this->error('No hay suscripción activa y no se pudo crear una temporal.');
            return;
        }

        // Asegurar que esté en grace period
        if (!$subscription->in_grace_period) {
            $subscription->enterGracePeriod();
            $subscription->save();
        }

        $subscription->load('plan');
        $this->info("Enviando webhook para suscripción ID: {$subscription->id}");
        
        $emailService->sendSubscriptionPaymentFailedEmail($company, $subscription);
        
        $this->info('✅ Webhook enviado correctamente. Revisa los logs y tu webhook de n8n.');
    }

    /**
     * Test webhook de suscripción cancelada
     */
    private function testCancelledWebhook(EmailService $emailService, Company $company): void
    {
        $this->info('Testing webhook: subscription_cancelled_webhook');

        // Buscar suscripción premium activa o crear una temporal
        $subscription = $company->activeSubscription;
        $premiumPlan = SubscriptionPlan::getPremiumPlan();

        if (!$subscription && $premiumPlan) {
            $this->warn('No hay suscripción activa. Creando una temporal para el test...');
            
            $endsAt = now()->addDays(7);
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $premiumPlan->id,
                'status' => 'cancelled',
                'current_period_start' => now()->subMonth(),
                'current_period_end' => $endsAt,
                'cancelled_at' => now(),
                'ends_at' => $endsAt,
            ]);

            $this->info("Suscripción temporal creada (ID: {$subscription->id})");
        } elseif (!$subscription) {
            $this->error('No hay suscripción activa y no se pudo crear una temporal.');
            return;
        }

        $subscription->load('plan');
        $this->info("Enviando webhook para suscripción ID: {$subscription->id}");
        
        $emailService->sendSubscriptionCancelledEmail($company, $subscription);
        
        $this->info('✅ Webhook enviado correctamente. Revisa los logs y tu webhook de n8n.');
    }
}


