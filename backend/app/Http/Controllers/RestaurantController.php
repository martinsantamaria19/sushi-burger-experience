<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Repositories\Interfaces\RestaurantRepositoryInterface;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    protected RestaurantRepositoryInterface $restaurantRepository;

    public function __construct(RestaurantRepositoryInterface $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }

    public function index(): JsonResponse
    {
        $restaurants = $this->restaurantRepository->all();
        return response()->json($restaurants);
    }

    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Initial defaults
            $data['settings'] = array_merge([
                'color_name' => '#ffffff',
                'color_address' => '#94a3b8',
                'color_btn_bg' => '#8738E1',
                'color_btn_text' => '#ffffff',
                'color_cat_title' => '#ffffff',
                'color_prod_title' => '#ffffff',
                'color_price' => '#8738E1',
                'color_card_bg' => '#121620',
                'color_bg' => '#07090e',
                'whatsapp' => '',
                'instagram' => '',
                'facebook' => ''
            ], $data['settings'] ?? []);

            $user = auth()->user();
            if (!$user->company) {
                return response()->json([
                    'message' => 'Usuario no tiene una compañía asignada'
                ], 400);
            }

            $company = $user->company;

            // Verificar límites del plan de suscripción
            if (!$company->canCreateRestaurant()) {
                $limit = $company->getRestaurantLimit();
                $current = $company->getRestaurantsCount();

                return response()->json([
                    'message' => 'Has alcanzado el límite de restaurantes permitidos en tu plan',
                    'error_code' => 'SUBSCRIPTION_LIMIT_EXCEEDED',
                    'limit_type' => 'restaurants',
                    'current' => $current,
                    'limit' => $limit,
                    'limit_reached' => true,
                    'upgrade_url' => route('admin.subscription'),
                ], 403);
            }

            $data['company_id'] = $company->id;
            $restaurant = $this->restaurantRepository->create($data);
            return response()->json($restaurant, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el restaurante: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $restaurant = $this->restaurantRepository->find($id);
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }
        return response()->json($restaurant);
    }

    public function update(UpdateRestaurantRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $restaurant = $this->restaurantRepository->find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store(
                'logos/' . $restaurant->id,
                'public'
            );

            // Guardar SOLO el path relativo
            $data['logo_path'] = $path;
        }

        // Handle Settings Merge
        $settings = $restaurant->settings ?? [];
        $settingsKeys = [
            'color_name', 'color_address', 'color_btn_bg', 'color_btn_text',
            'color_cat_title', 'color_prod_title', 'color_price', 'color_card_bg', 'color_bg',
            'whatsapp', 'instagram', 'facebook'
        ];

        foreach ($settingsKeys as $key) {
            if ($request->has($key)) {
                $settings[$key] = $request->get($key);
            }
        }

        $data['settings'] = $settings;

        $updated = $this->restaurantRepository->update($id, $data);
        return response()->json(['message' => 'Updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->restaurantRepository->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }
        return response()->json(['message' => 'Restaurant deleted successfully']);
    }
}
