<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = ItemCategory::query()->orderBy('name')->paginate((int) $request->get('per_page', 15));

        return $this->successResponse($categories, 'Categories retrieved successfully.');
    }

    public function show(ItemCategory $category): JsonResponse
    {
        return $this->successResponse($category, 'Category retrieved successfully.');
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $category = ItemCategory::create($request->validated());

        return $this->successResponse($category, 'Category created successfully.', 201);
    }

    public function update(CategoryUpdateRequest $request, ItemCategory $category): JsonResponse
    {
        $category->fill($request->validated())->save();

        return $this->successResponse($category, 'Category updated successfully.');
    }

    public function destroy(ItemCategory $category): JsonResponse
    {
        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully.');
    }

    protected function successResponse(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
