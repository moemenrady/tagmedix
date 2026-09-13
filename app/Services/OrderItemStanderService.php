<?php

namespace App\Services;

use App\Models\OrderItemStander;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderItemStanderService
{
    /**
     * Get all order item standards.
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return OrderItemStander::query()
            ->with([
                'pantType',
                'type',
                'style',
                'color',
                'size',
                'sleeveType',
            ])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find order item standard by ID.
     */
    public function find(int $id): OrderItemStander
    {
        return OrderItemStander::query()
            ->with([
                'pantType',
                'type',
                'style',
                'color',
                'size',
                'sleeveType',
            ])
            ->findOrFail($id);
    }

    /**
     * Create a new order item standard.
     */
    public function create(array $data): OrderItemStander
    {
        return DB::transaction(function () use ($data) {
            return OrderItemStander::create([
                'pant_type_id' => $data['pant_type_id'] ?? null,
                'type_id' => $data['type_id'] ?? null,
                'style_id' => $data['style_id'] ?? null,
                'color_id' => $data['color_id'] ?? null,
                'size_id' => $data['size_id'] ?? null,
                'sleeve_type_id' => $data['sleeve_type_id'] ?? null,
                'price' => $data['price'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an order item standard.
     */
    public function update(
        OrderItemStander $orderItemStander,
        array $data
    ): OrderItemStander {
        return DB::transaction(function () use (
            $orderItemStander,
            $data
        ) {
            $orderItemStander->update([
                'pant_type_id' => $data['pant_type_id'] ?? null,
                'type_id' => $data['type_id'] ?? null,
                'style_id' => $data['style_id'] ?? null,
                'color_id' => $data['color_id'] ?? null,
                'size_id' => $data['size_id'] ?? null,
                'sleeve_type_id' => $data['sleeve_type_id'] ?? null,
                'price' => $data['price'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $orderItemStander->fresh([
                'pantType',
                'type',
                'style',
                'color',
                'size',
                'sleeveType',
            ]);
        });
    }

    /**
     * Delete an order item standard.
     */
    public function delete(OrderItemStander $orderItemStander): bool
    {
        return DB::transaction(function () use ($orderItemStander) {
            return (bool) $orderItemStander->delete();
        });
    }

    /**
     * Change active status.
     */
    public function toggleStatus(
        OrderItemStander $orderItemStander
    ): OrderItemStander {
        $orderItemStander->update([
            'is_active' => ! $orderItemStander->is_active,
        ]);

        return $orderItemStander->fresh();
    }
}