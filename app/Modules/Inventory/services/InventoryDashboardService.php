<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Database\Eloquent\Collection;

final class InventoryDashboardService
{
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'items' => $this->getItems(),
        ];
    }

    private function getStats(): array
    {
        return [
            'total_items' => InventoryItem::query()
                ->where('is_active', true)
                ->count(),

            'total_variants' => InventoryItemVariant::query()
                ->where('is_active', true)
                ->count(),

            'total_units' => InventoryUnit::query()
                ->where('status', 'available')
                ->count(),

            'total_value' => $this->getTotalInventoryValue(),

            'low_stock_count' => $this->getLowStockCount(),
        ];
    }

    private function getTotalInventoryValue(): float
    {
        $bulkValue = InventoryStock::query()
            ->whereHas('inventoryItem', function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('stock_tracking_type', 'bulk');
            })
            ->sum('total_cost');

        $individualValue = InventoryUnit::query()
            ->where('status', 'available')
            ->whereHas('variant.inventoryItem', function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('stock_tracking_type', 'individual');
            })
            ->sum('total_cost');

        return (float) $bulkValue + (float) $individualValue;
    }

    private function getLowStockCount(): int
    {
        $items = $this->getItems();

        return $items
            ->filter(function (InventoryItem $item) {
                return $item->dashboard_quantity <= $item->minimum_stock;
            })
            ->count();
    }

    private function getItems(): Collection
    {
        $items = InventoryItem::query()
            ->where('is_active', true)
            ->with([
                'variants' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->with([
                            'inventoryItem',
                            'color',
                            'stocks',
                            'inventoryUnits',
                        ]);
                },
                'stocks',
            ])
            ->orderBy('name')
            ->get();

        foreach ($items as $item) {
            $this->calculateItemDashboardData($item);
        }

        return $items;
    }

    private function calculateItemDashboardData(
        InventoryItem $item
    ): void {
        if ($item->stock_tracking_type === 'individual') {
            $quantity = 0;
            $value = 0;

            foreach ($item->variants as $variant) {
                $variantQuantity = $variant->inventoryUnits
                    ->where('status', 'available')
                    ->sum('quantity');

                $variantValue = $variant->inventoryUnits
                    ->where('status', 'available')
                    ->sum('total_cost');

                $variant->dashboard_quantity = (float) $variantQuantity;
                $variant->dashboard_value = (float) $variantValue;
                $variant->available_units_count = $variant->inventoryUnits
                    ->where('status', 'available')
                    ->count();

                $quantity += $variantQuantity;
                $value += $variantValue;
            }

            $item->dashboard_quantity = (float) $quantity;
            $item->dashboard_value = (float) $value;

            return;
        }

        $quantity = $item->stocks->sum('quantity');
        $value = $item->stocks->sum('total_cost');

        foreach ($item->variants as $variant) {
            $variant->dashboard_quantity = (float) $variant->stocks
                ->sum('quantity');

            $variant->dashboard_value = (float) $variant->stocks
                ->sum('total_cost');

            $variant->available_units_count = 0;
        }

        $item->dashboard_quantity = (float) $quantity;
        $item->dashboard_value = (float) $value;
    }
}
