<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->categoryRepository->all());
    }

    public function store(\App\Http\Requests\StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryRepository->create($request->validated());
        return response()->json($category, 201);
    }

    public function show($id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        return $category ? response()->json($category) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $updated = $this->categoryRepository->update($id, $request->all());
        return $updated ? response()->json(['message' => 'Updated']) : response()->json(['message' => 'Not found'], 404);
    }

    public function destroy($id): JsonResponse
    {
        return $this->categoryRepository->delete($id) ? response()->json(['message' => 'Deleted']) : response()->json(['message' => 'Not found'], 404);
    }
}
