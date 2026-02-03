<?php

namespace App\Http\Controllers;

use App\Models\Company;
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
                    $q->orderBy('sort_order', 'asc');
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
}
