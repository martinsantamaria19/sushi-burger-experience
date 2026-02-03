<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan FREE
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => null, // Plan gratuito
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
            ]
        );

        // Plan PREMIUM
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 29.99, // Precio mensual - ajustar según necesidad
                'mp_subscription_id' => null, // Se configurará cuando se cree en MercadoPago
                'features' => [
                    'basic_menu',
                    'qr_code_generation',
                    'branding',
                    'analytics',
                    'unlimited_restaurants',
                    'unlimited_users',
                    'unlimited_qr_codes',
                    'custom_styling',
                    'advanced_reports',
                    'export_data',
                ],
                'limits' => [
                    'restaurants' => null, // Ilimitado
                    'users' => null, // Ilimitado
                    'qr_codes' => null, // Ilimitado
                ],
                'is_active' => true,
                'description' => 'Plan premium con todas las funcionalidades desbloqueadas',
            ]
        );

        $this->command->info('Planes de suscripción creados exitosamente!');
    }
}


