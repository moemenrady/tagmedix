<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryUnit;

class InventoryValuationService
{
    protected InventoryStockService $stockService;

    public function __construct(InventoryStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Calculate total inventory value.
     */
    public function getTotalInventoryValue(): float
    {
        return
            $this->getRawMaterialsValue()
            + $this->getAccessoriesValue()
            + $this->getFinishedProductsValue()
            + $this->getReadyOrdersValue();
    }

    /**
     * Calculate the current value of a specific inventory item.
     */
    public function getItemValue(InventoryItem $item): float
    {
        /*
         * Trackable items:
         * Example:
         * Rosaline Black
         * Top #001 = 64m
         * Top #002 = 50m
         *
         * We calculate the value from available units directly.
         */
        if ($item->track_as_units) {
            return (float) InventoryUnit::query()
                ->where('inventory_item_id', $item->id)
                ->where('status', 'available')
                ->sum('total_cost');
        }

        /*
         * Non-trackable items:
         * Example:
         * Tags = 3500 pieces
         *
         * Current stock × cost per unit.
         */
        $availableStock = $this->stockService->getAvailableStock($item->id);

        return (float) ($availableStock * (float) $item->cost_per_unit);
    }

    /**
     * Calculate inventory value by category type.
     *
     * Examples:
     * raw_material
     * accessory
     * finished_product
     * ready_order
     */
    public function getValueByCategoryType(string $categoryType): float
    {
        $items = InventoryItem::query()
            ->whereHas('category', function ($query) use ($categoryType) {
                $query->where('type', $categoryType);
            })
            ->get();

        $totalValue = 0.0;

        foreach ($items as $item) {
            $totalValue += $this->getItemValue($item);
        }

        return (float) $totalValue;
    }

    /**
     * Raw Materials value.
     */
    public function getRawMaterialsValue(): float
    {
        return $this->getValueByCategoryType('raw_material');
    }

    /**
     * Accessories value.
     */
    public function getAccessoriesValue(): float
    {
        return $this->getValueByCategoryType('accessory');
    }

    /**
     * Finished Products value.
     */
    public function getFinishedProductsValue(): float
    {
        return $this->getValueByCategoryType('finished_product');
    }

    /**
     * Ready Orders value.
     */
    public function getReadyOrdersValue(): float
    {
        return $this->getValueByCategoryType('ready_order');
    }

    /**
     * Dashboard statistics.
     */
    public function getDashboardStatistics(): array
    {
        return [
            'total_value' => $this->getTotalInventoryValue(),

            'raw_materials_value' => $this->getRawMaterialsValue(),

            'accessories_value' => $this->getAccessoriesValue(),

            'finished_products_value' => $this->getFinishedProductsValue(),

            'ready_orders_value' => $this->getReadyOrdersValue(),

            'total_trackable_units' => InventoryUnit::query()
                ->where('status', 'available')
                ->count(),

            'total_fabric_meter' => InventoryUnit::query()
                ->where('status', 'available')
                ->where('unit', 'meter')
                ->sum('quantity'),

            'total_fabric_kg' => InventoryUnit::query()
                ->where('status', 'available')
                ->where('unit', 'kg')
                ->sum('quantity'),
        ];
    }
}