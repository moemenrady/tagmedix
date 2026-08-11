<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(protected ItemService $itemService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'category',
            'sku',
            'barcode',
            'unit',
            'status',
            'available',
            'low_stock',
            'out_of_stock',
            'active',
            'inactive',
            'minimum_quantity',
            'quantity_from',
            'quantity_to',
            'created_from',
            'created_to',
            'sort_by',
            'sort_direction',
            'per_page',
        ]);

        return $this->successResponse($this->itemService->list($filters), 'Items retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        return $this->successResponse($this->itemService->show($id), 'Item retrieved successfully.');
    }

    public function store(ItemStoreRequest $request): JsonResponse
    {
        return $this->successResponse($this->itemService->create($request->validated()), 'Item created successfully.', 201);
    }

    public function update(ItemUpdateRequest $request, Item $item): JsonResponse
    {
        return $this->successResponse($this->itemService->update($item, $request->validated()), 'Item updated successfully.');
    }

    public function destroy(Item $item): JsonResponse
    {
        $this->itemService->delete($item);

        return $this->successResponse(null, 'Item deleted successfully.');
    }

    public function restore(int $id): JsonResponse
    {
        return $this->successResponse($this->itemService->restore($id), 'Item restored successfully.');
    }

    public function toggleStatus(Item $item): JsonResponse
    {
        return $this->successResponse($this->itemService->toggleStatus($item), 'Item status toggled successfully.');
    }

    public function statistics(): JsonResponse
    {
        return $this->successResponse($this->itemService->getStatistics(), 'Statistics retrieved successfully.');
    }

    public function lowStock(): JsonResponse
    {
        return $this->successResponse($this->itemService->getLowStock(), 'Low stock items retrieved successfully.');
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
