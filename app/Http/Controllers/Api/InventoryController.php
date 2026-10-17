<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementRequest;
use App\Http\Resources\InventoryTransactionResource;
use App\Models\Item;
use App\Services\InventoryReportService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryReportService $inventoryReportService,
    ) {
    }

    public function openingBalance(InventoryMovementRequest $request): JsonResponse
    {
        $resource = $this->inventoryService->openingBalance($request->validated(), $request->user()?->id);

        return $this->successResponse($resource, 'Opening balance created successfully.');
    }

    public function addStock(InventoryMovementRequest $request): JsonResponse
    {
        $resource = $this->inventoryService->addStock($request->validated(), $request->user()?->id);

        return $this->successResponse($resource, 'Stock added successfully.');
    }

    public function issueStock(InventoryMovementRequest $request): JsonResponse
    {
        $resource = $this->inventoryService->issueStock($request->validated(), $request->user()?->id);

        return $this->successResponse($resource, 'Stock issued successfully.');
    }

    public function returnStock(InventoryMovementRequest $request): JsonResponse
    {
        $resource = $this->inventoryService->returnStock($request->validated(), $request->user()?->id);

        return $this->successResponse($resource, 'Stock returned successfully.');
    }

    public function adjustStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'adjustment_type' => ['required', 'in:increase,decrease'],
            'notes' => ['nullable', 'string'],
            'reference_type' => ['nullable', 'string'],
            'reference_id' => ['nullable', 'integer'],
            'purpose' => ['nullable', 'string'],
        ]);

        $resource = $this->inventoryService->adjustStock($validated, $request->user()?->id);

        return $this->successResponse($resource, 'Stock adjusted successfully.');
    }

    public function inventoryCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $resource = $this->inventoryService->inventoryCount($validated, $request->user()?->id);

        return $this->successResponse($resource, 'Inventory count recorded successfully.');
    }

    public function rollbackTransaction(Request $request, int $transactionId): JsonResponse
    {
        $resource = $this->inventoryService->rollbackTransaction($transactionId);

        return $this->successResponse($resource, 'Transaction rolled back successfully.');
    }

    public function transactions(Request $request): JsonResponse
    {
        $filters = $request->only(['item_id', 'type', 'per_page']);
        $resource = $this->inventoryService->getTransactions($filters);

        return $this->successResponse($resource, 'Transactions retrieved successfully.');
    }

    public function itemTransactions(int $itemId): JsonResponse
    {
        $resource = $this->inventoryService->getItemTransactions($itemId);

        return $this->successResponse($resource, 'Item transactions retrieved successfully.');
    }

    public function movementSummary(int $itemId): JsonResponse
    {
        $resource = $this->inventoryService->getMovementSummary($itemId);

        return $this->successResponse($resource, 'Movement summary retrieved successfully.');
    }

    public function value(): JsonResponse
    {
        $resource = $this->inventoryService->getInventoryValue();

        return $this->successResponse($resource, 'Inventory value retrieved successfully.');
    }

    protected function successResponse(mixed $data, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
