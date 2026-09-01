<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Data\InventoryStockData;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryTransaction;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryReceivingService
{
    public function receive(
        InventoryStockData $data,
        int $userId
    ): InventoryItemVariant {
        return DB::transaction(function () use ($data, $userId) {

            $item = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($data->inventoryItemId);

            if (!$item->is_active) {
                throw ValidationException::withMessages([
                    'inventory_item_id' => 'Inventory item is inactive.',
                ]);
            }

            if (
                $item->stock_tracking_type !==
                $data->stockTrackingType
            ) {
                throw ValidationException::withMessages([
                    'stock_tracking_type' =>
                        'Stock tracking type does not match the item.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Variant
            |--------------------------------------------------------------------------
            */

            $variant = InventoryItemVariant::withTrashed()
                ->where('inventory_item_id', $item->id)
                ->where('color_id', $data->colorId)
                ->first();

            if ($variant) {
                if ($variant->trashed()) {
                    $variant->restore();
                }

                $variant->update([
                    'is_active' => true,
                ]);
            } else {
                $variant = InventoryItemVariant::create([
                    'inventory_item_id' => $item->id,
                    'color_id' => $data->colorId,
                    'sku' => $this->generateSku(
                        $item,
                        $data->colorId
                    ),
                    'is_active' => true,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Minimum Stock
            |--------------------------------------------------------------------------
            */

            if (
                (float) $item->minimum_stock !==
                $data->minimumStock
            ) {
                $item->update([
                    'minimum_stock' => $data->minimumStock,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            if ($data->stockTrackingType === 'bulk') {
                $this->receiveBulk(
                    item: $item,
                    variant: $variant,
                    data: $data,
                    userId: $userId
                );

                return $variant;
            }

            /*
            |--------------------------------------------------------------------------
            | Individual
            |--------------------------------------------------------------------------
            */

            $this->receiveIndividual(
                item: $item,
                variant: $variant,
                data: $data,
                userId: $userId
            );

            return $variant;
        });
    }

    private function receiveBulk(
        InventoryItem $item,
        InventoryItemVariant $variant,
        InventoryStockData $data,
        int $userId
    ): void {
        $quantity = $data->quantity;
        $unitCost = $data->unitCost;

        $totalCost = $quantity * $unitCost;

        $stock = InventoryStock::query()
            ->where('inventory_item_id', $item->id)
            ->where('inventory_item_variant_id', $variant->id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            /*
             * Weighted average cost
             */
            $oldQuantity = (float) $stock->quantity;
            $oldTotalCost = (float) $stock->total_cost;

            $newQuantity = $oldQuantity + $quantity;
            $newTotalCost = $oldTotalCost + $totalCost;

            $stock->update([
                'quantity' => $newQuantity,
                'unit_cost' => $newQuantity > 0
                    ? $newTotalCost / $newQuantity
                    : 0,
                'total_cost' => $newTotalCost,
            ]);
        } else {
            InventoryStock::create([
                'inventory_item_id' => $item->id,
                'inventory_item_variant_id' => $variant->id,
                'quantity' => $quantity,
                'unit_type' => $data->unitType,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);
        }

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,
            'inventory_unit_id' => null,
            'type' => 'receipt',
            'quantity' => $quantity,
            'unit_type' => $data->unitType,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'notes' => $data->notes,
            'created_by' => $userId,
            'transaction_date' => now(),
        ]);
    }

    private function receiveIndividual(
        InventoryItem $item,
        InventoryItemVariant $variant,
        InventoryStockData $data,
        int $userId
    ): void {
        foreach ($data->units as $unitData) {

            $unitCost = (float) $unitData['unit_cost'];
            $quantity = (float) $unitData['quantity'];

            $totalCost = $quantity * $unitCost;

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate unit codes
            |--------------------------------------------------------------------------
            */

            if (
                InventoryUnit::withTrashed()
                    ->where('unit_code', $unitData['unit_code'])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'units' =>
                        "Unit code {$unitData['unit_code']} already exists.",
                ]);
            }

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

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'inventory_item_variant_id' => $variant->id,
                'inventory_unit_id' => $unit->id,
                'type' => 'receipt',
                'quantity' => $quantity,
                'unit_type' => $unitData['unit_type'],
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $unitData['notes'] ?? null,
                'created_by' => $userId,
                'transaction_date' => now(),
            ]);
        }
    }

    private function generateSku(
        InventoryItem $item,
        int $colorId
    ): string {
        return $item->code . '-' . $colorId . '-' . strtoupper(
            substr(md5(uniqid()), 0, 6)
        );
    }
}