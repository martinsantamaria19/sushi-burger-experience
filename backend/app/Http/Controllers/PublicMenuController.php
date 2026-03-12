<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicMenuController extends Controller
{
    /**
     * Show the public menu for a restaurant or restaurant selector for a company.
     */
    public function show(string $slug): View
    {
        // Primero, intentar buscar una compañía por slug
        $company = Company::where('slug', $slug)->first();
        
        if ($company) {
            // Si encontramos una compañía, mostrar el selector de restaurantes
            $restaurants = $company->restaurants()
                ->where('is_active', true)
                ->where('is_blocked', false)
                ->orderBy('created_at', 'asc')
                ->get();
            
            return view('public.restaurants', compact('restaurants', 'company'));
        }
        
        // Si no es una compañía, buscar un restaurante por slug
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->with(['categories' => function($query) {
                $query->with(['products' => function($q) {
                    $q->with('variants')->orderBy('sort_order', 'asc');
                }])->orderBy('id', 'asc');
            }, 'company'])
            ->first();
        
        if (!$restaurant) {
            abort(404, 'Restaurante o compañía no encontrado');
        }
        
        $company = $restaurant->company;
        
        if (!$company) {
            abort(404, 'Restaurante no encontrado');
        }
        
        // Mostrar el menú del restaurante específico
        return view('public.menu', compact('restaurant', 'company'));
    }

    /**
     * Show individual product page with all variants and add to cart.
     */
    public function showProduct(string $restaurant_slug, int $product): View
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->with('company')
            ->first();

        if (!$restaurant) {
            abort(404, 'Restaurante no encontrado');
        }

        $productModel = Product::with('variants')
            ->where('id', $product)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (!$productModel || $productModel->trashed()) {
            abort(404, 'Producto no encontrado');
        }

        if (!$productModel->isAvailable()) {
            abort(404, 'Producto no disponible');
        }

        $company = $restaurant->company;
        if (!$company) {
            abort(404);
        }

        return view('public.product', [
            'product' => $productModel,
            'restaurant' => $restaurant,
            'company' => $company,
        ]);
    }
}
