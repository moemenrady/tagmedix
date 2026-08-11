<?php

namespace App\Services;

use App\Enums\InventoryReferenceType;
use App\Enums\InventoryTransactionType;
use App\Http\Resources\InventoryTransactionResource;
use App\Models\InventoryTransaction;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(protected ItemService $itemService)
    {
    }

    /**
     * Add stock for purchases or manual stock increase.
     */
    public function addStock(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];
            $unitCost = (float) ($data['unit_cost'] ?? 0);

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $balanceBefore + $quantity;
            $totalCost = round($quantity * $unitCost, 2);
            $averageCost = $this->calculateAverageCost($item, $quantity, $unitCost);
            $lastPurchasePrice = $unitCost;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => InventoryTransactionType::PURCHASE->value,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $data['reference_type'] ?? InventoryReferenceType::PURCHASE->value,
                'reference_id' => $data['reference_id'] ?? null,
                'purpose' => $data['purpose'] ?? 'stock_addition',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'average_cost' => $averageCost,
                'last_purchase_price' => $lastPurchasePrice,
                'total_inventory_cost' => round($balanceAfter * $averageCost, 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Issue stock to production or consumption.
     */
    public function issueStock(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            if ($item->current_quantity < $quantity) {
                throw new \RuntimeException('Insufficient stock available.');
            }

            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $balanceBefore - $quantity;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => InventoryTransactionType::PRODUCTION_ISSUE->value,
                'quantity' => -$quantity,
                'unit_cost' => $item->average_cost,
                'total_cost' => round($quantity * $item->average_cost, 2),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $data['reference_type'] ?? InventoryReferenceType::PRODUCTION_ORDER->value,
                'reference_id' => $data['reference_id'] ?? null,
                'purpose' => $data['purpose'] ?? 'production_issue',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'total_inventory_cost' => round($balanceAfter * $item->average_cost, 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Return stock from production to inventory.
     */
    public function returnStock(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $balanceBefore + $quantity;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => InventoryTransactionType::PRODUCTION_RETURN->value,
                'quantity' => $quantity,
                'unit_cost' => $item->average_cost,
                'total_cost' => round($quantity * $item->average_cost, 2),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $data['reference_type'] ?? InventoryReferenceType::PRODUCTION_ORDER->value,
                'reference_id' => $data['reference_id'] ?? null,
                'purpose' => $data['purpose'] ?? 'production_return',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'total_inventory_cost' => round($balanceAfter * $item->average_cost, 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Adjust stock manually.
     */
    public function adjustStock(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            $type = $data['adjustment_type'] ?? 'increase';
            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $type === 'decrease' ? $balanceBefore - $quantity : $balanceBefore + $quantity;

            if ($type === 'decrease' && $balanceAfter < 0) {
                throw new \RuntimeException('Stock cannot go below zero.');
            }

            $transactionType = $type === 'decrease' ? InventoryTransactionType::ADJUSTMENT_MINUS->value : InventoryTransactionType::ADJUSTMENT_ADD->value;
            $transactionQuantity = $type === 'decrease' ? -$quantity : $quantity;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => $transactionType,
                'quantity' => $transactionQuantity,
                'unit_cost' => $item->average_cost,
                'total_cost' => round($quantity * $item->average_cost, 2),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $data['reference_type'] ?? InventoryReferenceType::MANUAL->value,
                'reference_id' => $data['reference_id'] ?? null,
                'purpose' => $data['purpose'] ?? 'stock_adjustment',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'total_inventory_cost' => round($balanceAfter * $item->average_cost, 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Record opening balance.
     */
    public function openingBalance(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $balanceBefore + $quantity;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => InventoryTransactionType::OPENING_BALANCE->value,
                'quantity' => $quantity,
                'unit_cost' => $data['unit_cost'] ?? $item->average_cost,
                'total_cost' => round($quantity * ($data['unit_cost'] ?? $item->average_cost), 2),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => InventoryReferenceType::MANUAL->value,
                'reference_id' => null,
                'purpose' => 'opening_balance',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'average_cost' => $data['unit_cost'] ?? $item->average_cost,
                'total_inventory_cost' => round($balanceAfter * ($data['unit_cost'] ?? $item->average_cost), 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Record a stock count.
     */
    public function inventoryCount(array $data, ?int $userId = null): InventoryTransactionResource
    {
        return DB::transaction(function () use ($data, $userId) {
            $item = Item::findOrFail($data['item_id']);
            $quantity = (float) $data['quantity'];

            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $quantity;

            $transaction = $item->inventoryTransactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => InventoryTransactionType::INVENTORY_COUNT->value,
                'quantity' => $quantity - $balanceBefore,
                'unit_cost' => $item->average_cost,
                'total_cost' => round(($quantity - $balanceBefore) * $item->average_cost, 2),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => InventoryReferenceType::INVENTORY_COUNT->value,
                'reference_id' => null,
                'purpose' => 'inventory_count',
                'notes' => $data['notes'] ?? null,
                'performed_by' => $userId,
                'approved_by' => $data['approved_by'] ?? null,
                'transaction_date' => now(),
            ]);

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'total_inventory_cost' => round($balanceAfter * $item->average_cost, 2),
            ])->save();

            return new InventoryTransactionResource($transaction->load(['item', 'performedBy', 'approvedBy']));
        });
    }

    /**
     * Calculate weighted average cost.
     */
    public function calculateAverageCost(Item $item, float $quantity, float $unitCost): float
    {
        $existingQuantity = (float) $item->current_quantity;
        $existingCost = (float) $item->average_cost;
        $existingValue = $existingQuantity * $existingCost;
        $newValue = $quantity * $unitCost;

        if ($existingQuantity + $quantity <= 0) {
            return 0;
        }

        return round(($existingValue + $newValue) / ($existingQuantity + $quantity), 2);
    }

    /**
     * Get inventory transactions with pagination.
     */
    public function getTransactions(array $filters = []): AnonymousResourceCollection
    {
        $query = InventoryTransaction::query()->with(['item', 'performedBy', 'approvedBy']);

        if (! empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return InventoryTransactionResource::collection($query->orderByDesc('transaction_date')->paginate($perPage));
    }

    /**
     * Get transactions for a specific item.
     */
    public function getItemTransactions(int $itemId): AnonymousResourceCollection
    {
        $transactions = InventoryTransaction::query()
            ->with(['item', 'performedBy', 'approvedBy'])
            ->where('item_id', $itemId)
            ->orderByDesc('transaction_date')
            ->paginate(15);

        return InventoryTransactionResource::collection($transactions);
    }

    /**
     * Return movement summary for an item.
     */
    public function getMovementSummary(int $itemId): array
    {
        $item = Item::findOrFail($itemId);
        $transactions = InventoryTransaction::query()->where('item_id', $itemId);

        return [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'current_quantity' => (float) $item->current_quantity,
            'total_in' => (float) $transactions->where('quantity', '>', 0)->sum('quantity'),
            'total_out' => (float) abs($transactions->where('quantity', '<', 0)->sum('quantity')),
            'transaction_count' => (int) $transactions->count(),
        ];
    }

    /**
     * Get total inventory value.
     */
    public function getInventoryValue(): array
    {
        return [
            'total_inventory_value' => (float) Item::query()->sum('total_inventory_cost'),
        ];
    }

    /**
     * Recalculate an item from transactions.
     */
    public function recalculateItem(int $itemId): Item
    {
        return DB::transaction(function () use ($itemId) {
            $item = Item::findOrFail($itemId);
            $balance = 0.0;
            $latestCost = 0.0;

            foreach ($item->inventoryTransactions()->orderBy('id')->get() as $transaction) {
                $balance += (float) $transaction->quantity;
                $latestCost = $transaction->unit_cost;
            }

            $item->forceFill([
                'current_quantity' => $balance,
                'average_cost' => $latestCost,
                'total_inventory_cost' => round($balance * $latestCost, 2),
            ])->save();

            return $item;
        });
    }

    /**
     * Rollback the last transaction.
     */
    public function rollbackTransaction(int $transactionId): InventoryTransactionResource
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = InventoryTransaction::query()->with('item')->findOrFail($transactionId);
            $item = $transaction->item;
            $balanceBefore = (float) $item->current_quantity;
            $balanceAfter = $balanceBefore - (float) $transaction->quantity;

            if ($balanceAfter < 0) {
                throw new \RuntimeException('Rollback would make stock negative.');
            }

            $item->forceFill([
                'current_quantity' => $balanceAfter,
                'total_inventory_cost' => round($balanceAfter * $item->average_cost, 2),
            ])->save();

            $transaction->delete();

            return new InventoryTransactionResource($transaction->fresh());
        });
    }

    /**
     * Generate a transaction number.
     */
    protected function generateTransactionNumber(): string
    {
        $count = InventoryTransaction::query()->count() + 1;

        return 'TRX-' . str_pad((string) $count, 8, '0', STR_PAD_LEFT);
    }
}
