<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed subscription plans first
        $this->call([
            SubscriptionPlanSeeder::class,
        ]);

        // 1. Create a sample user if none exists
        $user = User::first() ?? User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@cartify.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Create a premium restaurant
        $restaurant = Restaurant::create([
            'name' => 'La Parrilla del Sol',
            'slug' => 'la-parrilla-del-sol',
            'address' => 'Av. Principal 123, Montevideo',
            'settings' => [
                'currency' => 'UYU',
                'primary_color' => '#7c3aed',
            ],
            'is_active' => true,
        ]);

        // 3. Link user to restaurant as owner
        $user->restaurants()->attach($restaurant->id, ['role' => 'owner']);

        // 4. Create a main menu
        $menu = Menu::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Menú Principal',
            'description' => 'Nuestra selección de carnes y vinos premium',
            'is_active' => true,
        ]);

        // 5. Create Categories
        $pizzasCat = Category::create([
            'restaurant_id' => $restaurant->id,
            'menu_id' => $menu->id,
            'name' => 'Carnes a la Parrilla',
            'sort_order' => 1,
        ]);

        $bebidasCat = Category::create([
            'restaurant_id' => $restaurant->id,
            'menu_id' => $menu->id,
            'name' => 'Bebidas & Vinos',
            'sort_order' => 2,
        ]);

        // 6. Create Products
        Product::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $pizzasCat->id,
            'name' => 'Asado de Tira',
            'description' => 'Corte premium de 500g a la leña',
            'price' => 850.00,
            'sort_order' => 1,
        ]);

        Product::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $pizzasCat->id,
            'name' => 'Pulpón de Vacío',
            'description' => 'Tierno y jugoso, selección especial',
            'price' => 780.00,
            'sort_order' => 2,
        ]);

        Product::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $bebidasCat->id,
            'name' => 'Tannat Reserva',
            'description' => 'Vino tinto nacional, cosecha 2022',
            'price' => 1200.00,
            'sort_order' => 1,
        ]);

        Product::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $bebidasCat->id,
            'name' => 'Agua Mineral',
            'description' => 'Con o sin gas 500ml',
            'price' => 95.00,
            'sort_order' => 2,
        ]);
    }
}
