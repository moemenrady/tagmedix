<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\CreateInventoryStockData;
use App\Modules\Inventory\Models\InventoryFinishedProduct;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryTransaction;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryStockInService
{
    public function execute(CreateInventoryStockData $data): InventoryItem
    {
        return DB::transaction(function () use ($data) {
            $this->validateBusinessRules($data);

            $item = $this->createItem($data);
            $variant = $this->createVariant($item, $data);

            if ($data->isFinishedProduct()) {

                $this->stockInFinishedProducts(
                    $item,
                    $variant,
                    $data
                );
            } elseif ($data->isBulk()) {

                $this->stockInBulk(
                    $item,
                    $variant,
                    $data
                );
            } elseif ($data->isIndividual()) {

                $this->stockInIndividual(
                    $item,
                    $variant,
                    $data
                );
            } else {

                throw ValidationException::withMessages([
                    'stock_tracking_type' => 'Unsupported stock tracking type.',
                ]);
            }

            return $item;
        });
    }

    private function validateBusinessRules(CreateInventoryStockData $data): void
    {if ($data->isFinishedProduct()) {

    if (empty($data->finishedProducts)) {
        throw ValidationException::withMessages([
            'items' => 'يجب إضافة منتج جاهز واحد على الأقل.',
        ]);
    }

    foreach ($data->finishedProducts as $product) {

        if ($product->colorId !== $data->colorId) {
            throw ValidationException::withMessages([
                'items' => 'لون المنتج الجاهز يجب أن يطابق اللون الرئيسي.',
            ]);
        }

        if ($product->quantity <= 0) {
            throw ValidationException::withMessages([
                'items' => 'كمية المنتج الجاهز يجب أن تكون أكبر من صفر.',
            ]);
        }
    }
}
        if ($data->isIndividual()) {
            foreach ($data->units as $unitData) {
                $exists = InventoryUnit::withTrashed()
                    ->where('unit_code', $unitData->unitCode)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'units' => "Unit code {$unitData->unitCode} already exists.",
                    ]);
                }
            }
        }
    }

    private function createItem(CreateInventoryStockData $data): InventoryItem
    {
        return InventoryItem::create([
            'code' => 'ITM-' . strtoupper(Str::random(6)),
            'name' => $data->name,
            'category' => $data->category,
            'unit_type' => $data->unitType,
            'stock_tracking_type' => $data->stockTrackingType,
            'is_active' => true,
            'minimum_stock' => $data->minimumStock,
            'created_by' => $data->createdBy,
        ]);
    }

    private function createVariant(InventoryItem $item, CreateInventoryStockData $data): InventoryItemVariant
    {
        return InventoryItemVariant::create([
            'inventory_item_id' => $item->id,
            'color_id' => $data->colorId,
            'sku' => 'VAR-' . strtoupper(Str::random(8)),
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk
    |--------------------------------------------------------------------------
    */

    private function stockInBulk(
        InventoryItem $item,
        InventoryItemVariant $variant,
        CreateInventoryStockData $data
    ): void {
        $quantity = $data->quantity ?? 0;
        $unitCost = $data->unitCost ?? 0;

        $totalCost = $quantity * $unitCost;

        InventoryStock::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_type' => $data->unitType,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
        ]);

        $balanceAfter = InventoryStock::query()
            ->where('inventory_item_id', $item->id)
            ->where('inventory_item_variant_id', $variant->id)
            ->sum('quantity');

        $this->createTransaction(
            item: $item,
            variant: $variant,
            unit: null,
            quantity: $quantity,
            unitType: (string) $data->unitType,
            unitCost: $unitCost,
            totalCost: $totalCost,
            notes: $data->notes,
            userId: $data->createdBy,
            balanceAfter: (float) $balanceAfter
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
        CreateInventoryStockData $data
    ): void {
        foreach ($data->units as $unitData) {
            $quantity = $unitData->quantity;
            $unitCost = $unitData->unitCost;

            $totalCost = $unitData->totalCost();

            $unit = InventoryUnit::create([
                'inventory_item_variant_id' => $variant->id,
                'unit_code' => $unitData->unitCode,
                'quantity' => $quantity,
                'unit_type' => $unitData->unitType,
                'status' => 'available',
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'received_at' => now(),
                'notes' => $unitData->notes,
            ]);

            $balanceAfter = InventoryUnit::query()
                ->where('inventory_item_variant_id', $variant->id)
                ->where('status', 'available')
                ->sum('quantity');

            $this->createTransaction(
                item: $item,
                variant: $variant,
                unit: $unit,
                quantity: $quantity,
                unitType: $unitData->unitType,
                unitCost: $unitCost,
                totalCost: $totalCost,
                notes: $unitData->notes,
                userId: $data->createdBy,
                balanceAfter: (float) $balanceAfter
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
        ?int $userId,
        float $balanceAfter
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
            'balance_after' => $balanceAfter,
            'notes' => $notes,
            'created_by' => $userId,
            'transaction_date' => now(),
        ]);
    }
    private function stockInFinishedProducts(
    InventoryItem $item,
    InventoryItemVariant $variant,
    CreateInventoryStockData $data
): void {
    foreach ($data->finishedProducts as $productData) {

        /*
        |--------------------------------------------------------------------------
        | Create Finished Product
        |--------------------------------------------------------------------------
        */

        $totalCost = $productData->totalCost();

        $finishedProduct = InventoryFinishedProduct::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,

            'product_code' => $this->generateFinishedProductCode(),

            'pant_type_id' => $productData->pantTypeId,
            'type_id' => $productData->typeId,
            'style_id' => $productData->styleId,
            'color_id' => $productData->colorId,
            'size_id' => $productData->sizeId,
            'sleeve_type_id' => $productData->sleeveTypeId,

            'quantity' => $productData->quantity,
            'unit_cost' => $productData->unitCost,
            'total_cost' => $totalCost,

            'notes' => $productData->notes,

            'is_active' => true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Inventory Stock
        |--------------------------------------------------------------------------
        */

        InventoryStock::create([
            'inventory_item_id' => $item->id,
            'inventory_item_variant_id' => $variant->id,

            'quantity' => $productData->quantity,
            'unit_type' => 'piece',

            'unit_cost' => $productData->unitCost,
            'total_cost' => $totalCost,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Calculate Balance
        |--------------------------------------------------------------------------
        */

        $balanceAfter = InventoryStock::query()
            ->where('inventory_item_id', $item->id)
            ->where('inventory_item_variant_id', $variant->id)
            ->sum('quantity');


        /*
        |--------------------------------------------------------------------------
        | Ledger Transaction
        |--------------------------------------------------------------------------
        */

        $this->createTransaction(
            item: $item,
            variant: $variant,
            unit: null,

            quantity: $productData->quantity,

            unitType: 'piece',

            unitCost: $productData->unitCost,

            totalCost: $totalCost,

            notes: $productData->notes,

            userId: $data->createdBy,

            balanceAfter: (float) $balanceAfter
        );
    }
}
private function generateFinishedProductCode(): string
{
    do {
        $code = 'FP-' . strtoupper(Str::random(8));
    } while (
        InventoryFinishedProduct::where('product_code', $code)
            ->exists()
    );

    return $code;
}
}
