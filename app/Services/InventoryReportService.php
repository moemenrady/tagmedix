<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Item;
use Illuminate\Support\Collection;

class InventoryReportService
{
    public function inventoryValuation(): array
    {
        return [
            'total_inventory_value' => (float) Item::query()->sum('total_inventory_cost'),
        ];
    }

    public function stockMovement(): array
    {
        return [
            'total_transactions' => InventoryTransaction::query()->count(),
            'total_in' => (float) InventoryTransaction::query()->where('quantity', '>', 0)->sum('quantity'),
            'total_out' => (float) abs(InventoryTransaction::query()->where('quantity', '<', 0)->sum('quantity')),
        ];
    }

    public function stockCard(int $itemId): array
    {
        $item = Item::findOrFail($itemId);

        return [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'current_quantity' => (float) $item->current_quantity,
            'transactions' => InventoryTransaction::query()->where('item_id', $itemId)->orderByDesc('transaction_date')->get(),
        ];
    }

    public function lowStock(): array
    {
        $items = Item::query()->whereColumn('current_quantity', '<=', 'minimum_quantity')->get();

        return [
            'count' => $items->count(),
            'items' => $items,
        ];
    }

    public function deadStock(): array
    {
        $items = Item::query()->where('current_quantity', '<=', 0)->get();

        return [
            'count' => $items->count(),
            'items' => $items,
        ];
    }

    public function monthlyConsumption(): array
    {
        return InventoryTransaction::query()
            ->where('quantity', '<', 0)
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, SUM(ABS(quantity)) as total_consumption')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function itemBalance(): array
    {
        return Item::query()->select('id', 'name', 'current_quantity', 'minimum_quantity')->get()->toArray();
    }
}
