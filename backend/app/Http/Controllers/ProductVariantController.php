<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $this->authorizeProduct($product);
        $variants = $product->variants()->orderBy('sort_order')->get();
        return response()->json($variants);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorizeProduct($product);
        $request->validate([
            'name' => 'required|string|max:255',
            'ingredients' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_gluten_free_available' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'ingredients', 'price', 'is_gluten_free_available', 'sort_order']);
        $data['is_gluten_free_available'] = (bool) ($data['is_gluten_free_available'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store(
                'products/' . $product->restaurant_id . '/variants',
                'public'
            );
            $data['image_path'] = $path;
        }

        $variant = $product->variants()->create($data);
        return response()->json($variant->fresh(), 201);
    }

    public function update(Request $request, ProductVariant $productVariant): JsonResponse
    {
        $this->authorizeProduct($productVariant->product);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'ingredients' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_gluten_free_available' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'ingredients', 'price', 'is_gluten_free_available', 'sort_order']);
        if (array_key_exists('is_gluten_free_available', $data)) {
            $data['is_gluten_free_available'] = (bool) $data['is_gluten_free_available'];
        }
        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store(
                'products/' . $productVariant->product->restaurant_id . '/variants',
                'public'
            );
            $data['image_path'] = $path;
        }

        $productVariant->update($data);
        return response()->json($productVariant->fresh());
    }

    public function destroy(ProductVariant $productVariant): JsonResponse
    {
        $this->authorizeProduct($productVariant->product);
        $productVariant->delete();
        return response()->json(['message' => 'Variante eliminada']);
    }

    private function authorizeProduct(Product $product): void
    {
        $user = auth()->user();
        if (!$user || !$user->company) {
            abort(403);
        }
        if (!$user->company->restaurants()->where('id', $product->restaurant_id)->exists()) {
            abort(403);
        }
    }
}
