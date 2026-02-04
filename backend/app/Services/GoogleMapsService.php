<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key', '');
    }

    /**
     * Obtiene la duración del trayecto en minutos entre origen y destino usando Directions API.
     * Origen y destino pueden ser direcciones textuales o "lat,lng".
     *
     * @return int|null Minutos del trayecto, o null si falla o no hay API key
     */
    public function getRouteDurationMinutes(string $origin, string $destination): ?int
    {
        $origin = trim($origin);
        $destination = trim($destination);

        if ($origin === '' || $destination === '') {
            return null;
        }

        if ($this->apiKey === '') {
            Log::warning('Google Maps API key not set. Skipping route duration.');
            return null;
        }

        $url = 'https://maps.googleapis.com/maps/api/directions/json';
        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'mode' => 'driving',
            'key' => $this->apiKey,
        ];

        try {
            $response = Http::timeout(10)->get($url, $params);

            if (!$response->successful()) {
                Log::warning('Google Directions API error', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            $status = $data['status'] ?? '';

            if ($status !== 'OK') {
                Log::debug('Google Directions API status', ['status' => $status]);
                return null;
            }

            $routes = $data['routes'] ?? [];
            $leg = $routes[0]['legs'][0] ?? null;

            if (!$leg || !isset($leg['duration']['value'])) {
                return null;
            }

            $durationSeconds = (int) $leg['duration']['value'];
            return (int) ceil($durationSeconds / 60);
        } catch (\Throwable $e) {
            Log::warning('Google Maps request failed', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
