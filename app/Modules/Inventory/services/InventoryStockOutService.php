<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryTransaction;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryStockOutService
{
    public function execute(
        array $data,
        int $userId
    ): void {
        DB::transaction(function () use ($data, $userId) {

            $item = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail(
                    $data['inventory_item_id']
                );

            if (!$item->is_active) {
                throw ValidationException::withMessages([
                    'inventory_item_id' =>
                        'This inventory item is inactive.',
                ]);
            }

            $variant = InventoryItemVariant::query()
                ->where(
                    'id',
                    $data['inventory_item_variant_id']
                )
                ->where(
                    'inventory_item_id',
                    $item->id
                )
                ->where('is_active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            if ($item->stock_tracking_type === 'bulk') {

                $this->stockOutBulk(
                    item: $item,
                    variant: $variant,
                    data: $data,
                    userId: $userId
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Individual
            |--------------------------------------------------------------------------
            */

            if ($item->stock_tracking_type === 'individual') {

                $this->stockOutIndividual(
                    item: $item,
                    variant: $variant,
                    data: $data,
                    userId: $userId
                );

                return;
            }

            throw ValidationException::withMessages([
                'inventory_item_id' =>
                    'Unsupported stock tracking type.',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | BULK
    |--------------------------------------------------------------------------
    */

    private function stockOutBulk(
        InventoryItem $item,
        InventoryItemVariant $variant,
        array $data,
        int $userId
    ): void {
        $quantity = (float) $data['quantity'];

        $stock = InventoryStock::query()
            ->where(
                'inventory_item_id',
                $item->id
            )
            ->where(
                'inventory_item_variant_id',
                $variant->id
            )
           
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw ValidationException::withMessages([
                'quantity' =>
                    'No stock available for this item.',
            ]);
        }

        $availableQuantity = (float) $stock->quantity;

        if ($quantity > $availableQuantity) {
            throw ValidationException::withMessages([
                'quantity' =>
                    "Insufficient stock. Available quantity: {$availableQuantity}.",
            ]);
        }

        $unitCost = (float) $stock->unit_cost;

        $totalCost = $quantity * $unitCost;

        $remainingQuantity =
            $availableQuantity - $quantity;

        $remainingTotalCost =
            $remainingQuantity * $unitCost;

        if ($remainingQuantity <= 0) {

            $stock->delete();

        } else {

            $stock->update([
                'quantity' => $remainingQuantity,
                'total_cost' => $remainingTotalCost,
            ]);
        }

        $this->createTransaction(
            item: $item,
            variant: $variant,
            unit: null,
            quantity: $quantity,
            unitType: $stock->unit_type,
            unitCost: $unitCost,
            totalCost: $totalCost,
            notes: $data['notes'] ?? null,
            userId: $userId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL
    |--------------------------------------------------------------------------
    */

    private function stockOutIndividual(
        InventoryItem $item,
        InventoryItemVariant $variant,
        array $data,
        int $userId
    ): void {
        $units = InventoryUnit::query()
            ->whereIn('id', $data['unit_ids'])
            ->where(
                'inventory_item_variant_id',
                $variant->id
            )
        
            ->where(
                'status',
                'available'
            )
            ->lockForUpdate()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Make sure all requested units exist
        |--------------------------------------------------------------------------
        */

        if ($units->count() !== count($data['unit_ids'])) {
            throw ValidationException::withMessages([
                'unit_ids' =>
                    'One or more selected units are no longer available.',
            ]);
        }

        foreach ($units as $unit) {

            $unit->update([
                'status' => 'issued',
            ]);

            $this->createTransaction(
                item: $item,
                variant: $variant,
                unit: $unit,
                quantity: (float) $unit->quantity,
                unitType: $unit->unit_type,
                unitCost: (float) $unit->unit_cost,
                totalCost: (float) $unit->total_cost,
                notes: $data['notes'] ?? null,
                userId: $userId
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    private function createTransaction(
        InventoryItem $item,
        InventoryItemVariant $variant,
        ?InventoryUnit $unit,
        float $quantity,
        string $unitType,
        float $unitCost,
        float $totalCost,
        ?string $notes,
        int $userId
    ): InventoryTransaction {
        return InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,
            'inventory_unit_id' => $unit?->id,
            'type' => 'issue',
            'quantity' => $quantity,
            'unit_type' => $unitType,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'notes' => $notes,
            'created_by' => $userId,
            'transaction_date' => now(),
        ]);
    }
}