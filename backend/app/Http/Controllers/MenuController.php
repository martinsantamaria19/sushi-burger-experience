<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected MenuRepositoryInterface $menuRepository;

    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->menuRepository->all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $menu = $this->menuRepository->create($validated);
        return response()->json($menu, 201);
    }

    public function show($id): JsonResponse
    {
        $menu = $this->menuRepository->find($id);
        return $menu ? response()->json($menu) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $updated = $this->menuRepository->update($id, $request->all());
        return $updated ? response()->json(['message' => 'Updated']) : response()->json(['message' => 'Not found'], 404);
    }

    public function destroy($id): JsonResponse
    {
        return $this->menuRepository->delete($id) ? response()->json(['message' => 'Deleted']) : response()->json(['message' => 'Not found'], 404);
    }
}
