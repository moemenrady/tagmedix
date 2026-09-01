<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryTransaction;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryStockInService
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
                ->where('id', $data['inventory_item_variant_id'])
                ->where('inventory_item_id', $item->id)
                ->where('is_active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            | Never trust stock_tracking_type from frontend.
            | Always get it from inventory_items.
            */

            if ($item->stock_tracking_type === 'bulk') {

                $this->stockInBulk(
                    item: $item,
                    variant: $variant,
                    data: $data,
                    userId: $userId
                );

                return;
            }

            if ($item->stock_tracking_type === 'individual') {

                $this->stockInIndividual(
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
    | Bulk
    |--------------------------------------------------------------------------
    */

    private function stockInBulk(
        InventoryItem $item,
        InventoryItemVariant $variant,
        array $data,
        int $userId
    ): void {
        $quantity = (float) $data['quantity'];
        $unitCost = (float) $data['unit_cost'];

        $totalCost = $quantity * $unitCost;

        $stock = InventoryStock::query()
            ->where('inventory_item_id', $item->id)
            ->where(
                'inventory_item_variant_id',
                $variant->id
            )
          
            ->lockForUpdate()
            ->first();

        if ($stock) {

            $oldQuantity = (float) $stock->quantity;
            $oldTotalCost = (float) $stock->total_cost;

            $newQuantity = $oldQuantity + $quantity;
            $newTotalCost = $oldTotalCost + $totalCost;

            $averageCost = $newQuantity > 0
                ? $newTotalCost / $newQuantity
                : 0;

            $stock->update([
                'quantity' => $newQuantity,
                'unit_cost' => $averageCost,
                'total_cost' => $newTotalCost,
            ]);

        } else {

            InventoryStock::create([
                'inventory_item_id' => $item->id,
                'inventory_item_variant_id' => $variant->id,
                'quantity' => $quantity,
                'unit_type' => $data['unit_type'],
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);
        }

        $this->createTransaction(
            item: $item,
            variant: $variant,
            unit: null,
            quantity: $quantity,
            unitType: $data['unit_type'],
            unitCost: $unitCost,
            totalCost: $totalCost,
            notes: $data['notes'] ?? null,
            userId: $userId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Individual
    |--------------------------------------------------------------------------
    */

    private function stockInIndividual(
        InventoryItem $item,
        InventoryItemVariant $variant,
        array $data,
        int $userId
    ): void {
        foreach ($data['units'] as $unitData) {

            /*
            |--------------------------------------------------------------------------
            | Unit Code must be globally unique
            |--------------------------------------------------------------------------
            */

            $exists = InventoryUnit::withTrashed()
                ->where(
                    'unit_code',
                    $unitData['unit_code']
                )
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'units' =>
                        "Unit code {$unitData['unit_code']} already exists.",
                ]);
            }

            $quantity = (float) $unitData['quantity'];
            $unitCost = (float) $unitData['unit_cost'];

            $totalCost = $quantity * $unitCost;

            $unit = InventoryUnit::create([
                'inventory_item_variant_id' => $variant->id,
                'unit_code' => $unitData['unit_code'],
                'quantity' => $quantity,
                'unit_type' => $unitData['unit_type'],
                'status' => 'available',
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'received_at' => now(),
                'notes' => $unitData['notes'] ?? null,
            ]);

            $this->createTransaction(
                item: $item,
                variant: $variant,
                unit: $unit,
                quantity: $quantity,
                unitType: $unitData['unit_type'],
                unitCost: $unitCost,
                totalCost: $totalCost,
                notes: $unitData['notes'] ?? null,
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
            'type' => 'receipt',
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