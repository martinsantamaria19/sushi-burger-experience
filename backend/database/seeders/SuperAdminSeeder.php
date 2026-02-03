<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un superadmin
        $existingSuperAdmin = User::where('super_admin', true)->first();

        if ($existingSuperAdmin) {
            $this->command->info('Ya existe un usuario superadmin con email: ' . $existingSuperAdmin->email);
            return;
        }

        // Crear usuario superadmin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sushiburger.com',
            'password' => Hash::make('admin123'),
            'super_admin' => true,
            'is_owner' => false,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Usuario superadmin creado exitosamente!');
        $this->command->info('   Email: admin@sushiburger.com');
        $this->command->info('   Password: admin123');
        $this->command->warn('   ⚠️  IMPORTANTE: Cambia la contraseña después del primer inicio de sesión');
    }
}
