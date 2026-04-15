<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevelopmentMode
{
    /**
     * Handle an incoming request.
     *
     * If development mode is enabled for the main company, show a simple
     * "sitio en construcción" page to usuarios no logueados. Usuarios
     * autenticados pueden seguir usando el panel normalmente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir siempre el backend de autenticación
        if ($request->is('login') || $request->is('register') || $request->is('forgot-password*') || $request->is('reset-password*')) {
            return $next($request);
        }

        // Permitir siempre webhooks y callbacks externos (MercadoPago, etc.)
        if ($request->is('api/webhooks/*')) {
            return $next($request);
        }

        // Permitir siempre rutas del panel (protegidas por auth)
        if ($request->is('admin/*') || $request->is('dashboard*') || $request->is('dashboard-api/*') || $request->is('super-admin/*')) {
            return $next($request);
        }

        // Si el usuario ya está logueado, permitir acceso normal
        if (auth()->check()) {
            return $next($request);
        }

        // Buscar compañía principal
        $company = Company::first();
        if (!$company || !$company->isInDevelopmentMode()) {
            return $next($request);
        }

        // Mostrar página de sitio en construcción para el resto de rutas públicas
        return response()->view('public.maintenance', [
            'company' => $company,
        ], 503);
    }
}

