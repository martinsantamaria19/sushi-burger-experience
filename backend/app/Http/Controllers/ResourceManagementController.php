<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Restaurant;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ResourceManagementController extends Controller
{
    /**
     * Mostrar vista para gestionar recursos bloqueados cuando se vuelve a free
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes una compañía asignada.');
        }

        // Solo mostrar si está en plan free y tiene recursos bloqueados
        if (!$company->isOnFreePlan()) {
            return redirect()->route('admin.subscription')
                ->with('info', 'Solo puedes gestionar recursos bloqueados cuando estás en plan Free.');
        }

        $limits = $company->currentPlan->getLimits();
        
        // Obtener recursos bloqueados
        $blockedRestaurants = $company->restaurants()->where('is_blocked', true)->get();
        $blockedQrCodes = QrCode::whereIn('restaurant_id', $company->restaurants->pluck('id'))
            ->where('is_blocked', true)
            ->with('restaurant')
            ->get();
        $blockedUsers = $company->users()
            ->where('is_owner', false)
            ->where('is_blocked', true)
            ->get();

        // Contar recursos activos
        $activeRestaurants = $company->restaurants()->where('is_blocked', false)->count();
        $activeQrCodes = $company->getTotalQrCodesCount() - $blockedQrCodes->count();
        $activeUsers = $company->users()
            ->where('is_owner', false)
            ->where('is_blocked', false)
            ->count();

        return view('admin.resource-management', [
            'company' => $company,
            'limits' => $limits,
            'blockedRestaurants' => $blockedRestaurants,
            'blockedQrCodes' => $blockedQrCodes,
            'blockedUsers' => $blockedUsers,
            'activeRestaurants' => $activeRestaurants,
            'activeQrCodes' => $activeQrCodes,
            'activeUsers' => $activeUsers,
        ]);
    }

    /**
     * Actualizar recursos activos/bloqueados
     */
    public function updateResources(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company || !$company->isOnFreePlan()) {
            return response()->json([
                'message' => 'Solo puedes gestionar recursos cuando estás en plan Free.'
            ], 403);
        }

        $limits = $company->currentPlan->getLimits();
        $restaurantLimit = $limits['restaurants'] ?? 1;
        $qrCodeLimit = $limits['qr_codes'] ?? 2;
        $userLimit = $limits['users'] ?? 1;

        // Validar límites
        $activeRestaurantIds = $request->input('active_restaurants', []);
        $activeQrCodeIds = $request->input('active_qr_codes', []);
        $activeUserIds = $request->input('active_users', []);

        if (count($activeRestaurantIds) > $restaurantLimit) {
            return response()->json([
                'message' => "El plan Free permite máximo {$restaurantLimit} restaurante(s)."
            ], 400);
        }

        if (count($activeQrCodeIds) > $qrCodeLimit) {
            return response()->json([
                'message' => "El plan Free permite máximo {$qrCodeLimit} código(s) QR."
            ], 400);
        }

        if (count($activeUserIds) > $userLimit) {
            return response()->json([
                'message' => "El plan Free permite máximo {$userLimit} usuario(s)."
            ], 400);
        }

        // Bloquear/desbloquear restaurants
        $company->restaurants()->each(function ($restaurant) use ($activeRestaurantIds) {
            if (in_array($restaurant->id, $activeRestaurantIds)) {
                $restaurant->unblock();
            } else {
                $restaurant->block('subscription_limit');
            }
        });

        // Bloquear/desbloquear QR codes
        $restaurantIds = $company->restaurants->pluck('id');
        if ($restaurantIds->isNotEmpty()) {
            QrCode::whereIn('restaurant_id', $restaurantIds)->each(function ($qrCode) use ($activeQrCodeIds) {
                if (in_array($qrCode->id, $activeQrCodeIds)) {
                    $qrCode->unblock();
                } else {
                    $qrCode->block('subscription_limit');
                }
            });
        }

        // Bloquear/desbloquear usuarios (excepto owners)
        $company->users()
            ->where('is_owner', false)
            ->each(function ($user) use ($activeUserIds) {
                if (in_array($user->id, $activeUserIds)) {
                    $user->unblock();
                } else {
                    $user->block('subscription_limit');
                }
            });

        Log::info('Resources updated after plan downgrade', [
            'company_id' => $company->id,
            'active_restaurants' => count($activeRestaurantIds),
            'active_qr_codes' => count($activeQrCodeIds),
            'active_users' => count($activeUserIds),
        ]);

        return response()->json([
            'message' => 'Recursos actualizados correctamente',
            'redirect' => route('admin.dashboard'),
        ]);
    }
}
