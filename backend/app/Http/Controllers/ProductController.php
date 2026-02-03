<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(): JsonResponse
    {
        $products = $this->productRepository->all();
        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store(
                'products/' . $data['restaurant_id'],
                'public'
            );

            // Guardar SOLO el path relativo
            $data['image_path'] = $path;
        }

        $product = $this->productRepository->create($data);

        return response()->json($product, 201);
    }


    public function show($id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $product = $this->productRepository->find($id);

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = 'uploads/products/' . ($product->restaurant_id ?? 'unknown');
            $file->move(public_path($path), $filename);
            $data['image_path'] = asset($path . '/' . $filename);
        }

        $updated = $this->productRepository->update($id, $data);
        if (!$updated) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['message' => 'Product updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->productRepository->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
