<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SiteSettingsSeeder::class,
        ]);

        if (User::where('email', 'admin@boxcenter.com.uy')->doesntExist()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@boxcenter.com.uy',
                'password' => bcrypt('password'),
            ]);
        }
    }
}
