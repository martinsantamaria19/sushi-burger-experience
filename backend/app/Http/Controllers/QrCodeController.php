<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    /**
     * Display a listing of QR codes for a restaurant.
     */
    public function index(Request $request): JsonResponse
    {
        $restaurantId = $request->query('restaurant_id');
        $qrs = QrCode::where('restaurant_id', $restaurantId)
            ->with('restaurant:id,name')
            ->withCount('scans')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($qrs);
    }

    /**
     * Store a newly created QR code.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        if (!$user->company) {
            return response()->json([
                'message' => 'Usuario no tiene una compañía asignada'
            ], 400);
        }

        $company = $user->company;

        // Verificar límites del plan de suscripción para QR codes
        if (!$company->canCreateQrCode()) {
            $limit = $company->getQrCodeLimit();
            $current = $company->getTotalQrCodesCount();
            
            return response()->json([
                'message' => 'Has alcanzado el límite de códigos QR permitidos en tu plan',
                'error_code' => 'SUBSCRIPTION_LIMIT_EXCEEDED',
                'limit_type' => 'qr_codes',
                'current' => $current,
                'limit' => $limit,
                'limit_reached' => true,
                'upgrade_url' => route('admin.subscription'),
            ], 403);
        }

        $qr = QrCode::create([
            'restaurant_id' => $request->restaurant_id,
            'name' => $request->name,
            'redirect_slug' => Str::random(8), // Unique slug for /scan/{slug}
            'is_active' => true,
        ]);

        return response()->json($qr, 201);
    }

    /**
     * Remove the specified QR code.
     */
    public function destroy(string $id): JsonResponse
    {
        $qr = QrCode::findOrFail($id);
        $qr->delete();

        return response()->json(null, 204);
    }
}
