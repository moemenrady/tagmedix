<?php

namespace App\Services;

use App\Http\Resources\ItemCollection;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemService
{
    /**
     * Create a new item.
     */
    public function create(array $data): ItemResource
    {
        return DB::transaction(function () use ($data) {
            $item = Item::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'barcode' => $data['barcode'] ?? null,
                'category_id' => $data['category_id'],
                'unit' => $data['unit'] ?? null,
                'description' => $data['description'] ?? null,
                'minimum_quantity' => $data['minimum_quantity'] ?? 0,
                'current_quantity' => $data['current_quantity'] ?? 0,
                'average_cost' => $data['average_cost'] ?? 0,
                'last_purchase_price' => $data['last_purchase_price'] ?? 0,
                'total_inventory_cost' => $data['total_inventory_cost'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return new ItemResource($item->load('category'));
        });
    }

    /**
     * Update an existing item.
     */
    public function update(Item $item, array $data): ItemResource
    {
        return DB::transaction(function () use ($item, $data) {
            $item->fill(Arr::except($data, ['current_quantity', 'average_cost', 'last_purchase_price', 'total_inventory_cost']))->save();

            return new ItemResource($item->fresh()->load('category'));
        });
    }

    /**
     * Soft delete an item.
     */
    public function delete(Item $item): bool
    {
        return DB::transaction(function () use ($item) {
            return (bool) $item->delete();
        });
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(int $id): ItemResource
    {
        return DB::transaction(function () use ($id) {
            $item = Item::withTrashed()->findOrFail($id);
            $item->restore();

            return new ItemResource($item->fresh()->load('category'));
        });
    }

    /**
     * Toggle the item active status.
     */
    public function toggleStatus(Item $item): ItemResource
    {
        return DB::transaction(function () use ($item) {
            $item->is_active = ! $item->is_active;
            $item->save();

            return new ItemResource($item->fresh()->load('category'));
        });
    }

    /**
     * Find an item by ID.
     */
    public function find(int $id): ItemResource
    {
        $item = Item::with('category')->findOrFail($id);

        return new ItemResource($item);
    }

    /**
     * Show an item by ID.
     */
    public function show(int $id): ItemResource
    {
        return $this->find($id);
    }

    /**
     * List items with optional filters and search.
     */
    public function list(array $filters = []): array
    {
        $query = Item::query()->with('category');

        $query = $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 15);
        $paginator = $query->paginate($perPage);

        return [
            'data' => $paginator->getCollection()->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category?->name,
                'unit' => $item->unit,
                'current_quantity' => (float) $item->current_quantity,
                'minimum_quantity' => (float) $item->minimum_quantity,
                'last_purchase_price' => (float) $item->last_purchase_price,
                'inventory_value' => (float) $item->total_inventory_cost,
                'status' => $item->current_quantity <= 0 ? 'out_of_stock' : ($item->current_quantity <= $item->minimum_quantity ? 'low_stock' : 'available'),
            ])->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Search items by name, SKU or barcode.
     */
    public function search(string $term): array
    {
        $query = Item::query()->with('category')
            ->where('name', 'like', "%{$term}%")
            ->orWhere('code', 'like', "%{$term}%")
            ->orWhere('barcode', 'like', "%{$term}%")
            ->orderBy('name');

        $paginator = $query->paginate(15);

        return [
            'data' => $paginator->getCollection()->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category?->name,
                'unit' => $item->unit,
                'current_quantity' => (float) $item->current_quantity,
                'minimum_quantity' => (float) $item->minimum_quantity,
                'last_purchase_price' => (float) $item->last_purchase_price,
                'inventory_value' => (float) $item->total_inventory_cost,
                'status' => $item->current_quantity <= 0 ? 'out_of_stock' : ($item->current_quantity <= $item->minimum_quantity ? 'low_stock' : 'available'),
            ])->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Filter items based on query parameters.
     */
    public function filter(array $filters = []): array
    {
        return $this->list($filters);
    }

    /**
     * Get low stock items.
     */
    public function getLowStock(): array
    {
        $paginator = Item::query()->with('category')
            ->whereColumn('current_quantity', '<=', 'minimum_quantity')
            ->orderBy('name')
            ->paginate(15);

        return [
            'data' => $paginator->getCollection()->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category?->name,
                'unit' => $item->unit,
                'current_quantity' => (float) $item->current_quantity,
                'minimum_quantity' => (float) $item->minimum_quantity,
                'last_purchase_price' => (float) $item->last_purchase_price,
                'inventory_value' => (float) $item->total_inventory_cost,
                'status' => $item->current_quantity <= 0 ? 'out_of_stock' : ($item->current_quantity <= $item->minimum_quantity ? 'low_stock' : 'available'),
            ])->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Return inventory statistics.
     */
    public function getStatistics(): array
    {
        $items = Item::query();

        return [
            'total_items' => (int) $items->count(),
            'total_inventory_value' => (float) $items->sum('total_inventory_cost'),
            'low_stock_items' => (int) $items->whereColumn('current_quantity', '<=', 'minimum_quantity')->count(),
            'out_of_stock_items' => (int) $items->where('current_quantity', '<=', 0)->count(),
            'total_available_quantity' => (float) $items->sum('current_quantity'),
        ];
    }

    /**
     * Update averages and values. This is intentionally a no-op here because inventory service owns the movement math.
     */
    public function updateAverageCost(Item $item, float $averageCost): void
    {
        $item->forceFill([
            'average_cost' => $averageCost,
        ])->save();
    }

    /**
     * Recalculate inventory value.
     */
    public function recalculateInventoryValue(Item $item): void
    {
        $item->forceFill([
            'total_inventory_cost' => round($item->current_quantity * $item->average_cost, 2),
        ])->save();
    }

    /**
     * Apply search and filter logic.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    protected function applyFilters($query, array $filters = []): mixed
    {
        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhere('unit', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('name', 'like', '%' . $filters['category'] . '%'));
        }

        if (! empty($filters['sku'])) {
            $query->where('code', 'like', '%' . $filters['sku'] . '%');
        }

        if (! empty($filters['barcode'])) {
            $query->where('barcode', 'like', '%' . $filters['barcode'] . '%');
        }

        if (! empty($filters['unit'])) {
            $query->where('unit', 'like', '%' . $filters['unit'] . '%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['available']) && $filters['available'] !== '') {
            $query->where('current_quantity', '>', 0);
        }

        if (isset($filters['low_stock']) && $filters['low_stock'] !== '') {
            $query->whereColumn('current_quantity', '<=', 'minimum_quantity');
        }

        if (isset($filters['out_of_stock']) && $filters['out_of_stock'] !== '') {
            $query->where('current_quantity', '<=', 0);
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('is_active', true);
        }

        if (isset($filters['inactive']) && $filters['inactive'] !== '') {
            $query->where('is_active', false);
        }

        if (! empty($filters['minimum_quantity'])) {
            $query->where('minimum_quantity', '>=', $filters['minimum_quantity']);
        }

        if (! empty($filters['quantity_from'])) {
            $query->where('current_quantity', '>=', $filters['quantity_from']);
        }

        if (! empty($filters['quantity_to'])) {
            $query->where('current_quantity', '<=', $filters['quantity_to']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        if (! empty($filters['sort_by'])) {
            $direction = $filters['sort_direction'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('name');
        }

        return $query;
    }
}
