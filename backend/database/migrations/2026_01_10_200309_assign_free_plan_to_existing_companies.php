<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPlan;
use App\Models\Company;
use App\Models\Subscription;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener el plan FREE (debe existir por el seeder)
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();

        if (!$freePlan) {
            // Si el plan no existe, crearlo
            $freePlan = SubscriptionPlan::create([
                'name' => 'Free',
                'slug' => 'free',
                'price' => null,
                'mp_subscription_id' => null,
                'features' => [
                    'basic_menu',
                    'qr_code_generation',
                ],
                'limits' => [
                    'restaurants' => 1,
                    'users' => 1,
                    'qr_codes' => 2,
                ],
                'is_active' => true,
                'description' => 'Plan gratuito con funcionalidades básicas',
            ]);
        }

        // Asignar plan FREE a todas las companies que no tengan plan asignado
        $companies = Company::whereNull('plan_id')->get();

        foreach ($companies as $company) {
            // Actualizar el plan_id de la company
            $company->plan_id = $freePlan->id;
            $company->save();

            // Crear una suscripción activa para la company (si no existe)
            if (!$company->subscription_id) {
                $subscription = Subscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'mp_subscription_id' => null,
                    'mp_preapproval_id' => null,
                    'current_period_start' => now(),
                    'current_period_end' => now()->addYear(), // Suscripción free sin fecha de expiración efectiva
                    'trial_ends_at' => null,
                    'cancelled_at' => null,
                    'ends_at' => null,
                ]);

                // Asignar la suscripción a la company
                $company->subscription_id = $subscription->id;
                $company->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En el rollback, podrías quitar las suscripciones y planes asignados
        // Pero generalmente no es necesario hacer nada aquí
        // ya que el rollback de las migraciones anteriores eliminará las tablas
    }
};

