<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\GoogleMapsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryEstimateController extends Controller
{
    public function __construct(
        protected GoogleMapsService $googleMaps
    ) {}

    /**
     * Estima el tiempo de entrega: tiempo de preparación + tiempo de ruta (Google Directions).
     * GET /api/delivery-estimate?restaurant_id=1&destination=Av.+18+de+Julio+1234,+Montevideo
     */
    public function estimate(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'destination' => 'required|string|max:500',
        ]);

        $restaurant = Restaurant::find($request->restaurant_id);
        // Usar coordenadas del restaurante cuando existan (más preciso que la dirección en texto)
        $origin = null;
        if ($restaurant->latitude !== null && $restaurant->longitude !== null) {
            $origin = $restaurant->latitude . ',' . $restaurant->longitude;
        }
        if (empty($origin)) {
            $origin = $restaurant->address;
        }
        if (empty($origin)) {
            return response()->json([
                'ok' => false,
                'message' => 'El restaurante no tiene dirección ni coordenadas configuradas.',
                'preparation_minutes' => $restaurant->preparation_time_minutes ?? 15,
                'route_minutes' => null,
                'total_minutes' => null,
            ], 422);
        }

        $preparationMinutes = (int) ($restaurant->preparation_time_minutes ?? 15);
        $routeMinutes = $this->googleMaps->getRouteDurationMinutes($origin, $request->destination);

        if ($routeMinutes === null) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo calcular la ruta. Verificá la dirección o intentá más tarde.',
                'preparation_minutes' => $preparationMinutes,
                'route_minutes' => null,
                'total_minutes' => null,
            ], 200);
        }

        $totalMinutes = $preparationMinutes + $routeMinutes;

        return response()->json([
            'ok' => true,
            'message' => "Tiempo estimado de entrega: aproximadamente {$totalMinutes} minutos.",
            'preparation_minutes' => $preparationMinutes,
            'route_minutes' => $routeMinutes,
            'total_minutes' => $totalMinutes,
        ]);
    }
}
