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
            ->where('inventory_item_id', $item->id)
            ->where('inventory_item_variant_id', $variant->id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw ValidationException::withMessages([
                'quantity' => 'No stock available for this item.',
            ]);
        }

        $availableQuantity = (float) $stock->quantity;

        if ($quantity > $availableQuantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock. Available quantity: {$availableQuantity}.",
            ]);
        }

        $unitCost = (float) $stock->unit_cost;
        $totalCost = $quantity * $unitCost;
        $remainingQuantity = $availableQuantity - $quantity;
        $remainingTotalCost = $remainingQuantity * $unitCost;

        if ($remainingQuantity <= 0) {
            $stock->delete();
        } else {
            $stock->update([
                'quantity' => $remainingQuantity,
                'total_cost' => $remainingTotalCost,
            ]);
        }

        $balanceAfter = InventoryStock::query()
            ->where('inventory_item_id', $item->id)
            ->where('inventory_item_variant_id', $variant->id)
            ->sum('quantity');

        $this->createTransaction(
            item: $item,
            variant: $variant,
            unit: null,
            quantity: $quantity,
            unitType: $stock->unit_type,
            unitCost: $unitCost,
            totalCost: $totalCost,
            notes: $data['notes'] ?? null,
            userId: $userId,
            balanceAfter: (float) $balanceAfter,
            type: $data['type'] ?? 'issue'
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
        $unitIds = $data['unit_ids'] ?? [];

        if (empty($unitIds)) {
            throw ValidationException::withMessages([
                'inventory_unit_id' => 'لم يتم تحديد أي وحدة/توب للصرف.',
            ]);
        }

        $units = InventoryUnit::query()
            ->whereIn('id', $unitIds)
            ->where('inventory_item_variant_id', $variant->id)
            ->where('status', 'available')
            ->lockForUpdate()
            ->get();

        if ($units->count() !== count($unitIds)) {
            throw ValidationException::withMessages([
                'inventory_unit_id' => 'الوحدة المحددة غير متاحة أو تم صرفها مسبقاً.',
            ]);
        }

        // تحديد حالة التوب الجديدة بناءً على نوع الحركة
        $newStatus = match ($data['type'] ?? 'issue') {
            'damage' => 'damaged',
            default => 'issued',
        };

        foreach ($units as $unit) {
            // 1. تغيير حالة التوب
            $unit->update([
                'status' => $newStatus,
            ]);

            // 2. حساب رصيد التوبات المتبقية المتاحة
            $balanceAfter = InventoryUnit::query()
                ->where('inventory_item_variant_id', $variant->id)
                ->where('status', 'available')
                ->sum('quantity');

            // 3. إنشـاء حركة الصرف
            $this->createTransaction(
                item: $item,
                variant: $variant,
                unit: $unit,
                quantity: (float) $unit->quantity,
                unitType: $unit->unit_type,
                unitCost: (float) $unit->unit_cost,
                totalCost: (float) $unit->total_cost,
                notes: $data['notes'] ?? null,
                userId: $userId,
                balanceAfter: (float) $balanceAfter,
                type: $data['type'] ?? 'issue'
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
        int $userId,
        float $balanceAfter,
        string $type = 'issue'
    ): InventoryTransaction {
        return InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,
            'inventory_unit_id' => $unit?->id,
            'type' => $type,
            'quantity' => $quantity,
            'unit_type' => $unitType,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'balance_after' => $balanceAfter,
            'notes' => $notes,
            'created_by' => $userId,
            'transaction_date' => now(),
        ]);
    }
}
