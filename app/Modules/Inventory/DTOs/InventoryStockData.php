<?php

namespace App\Modules\Inventory\Data;

class InventoryStockData
{
    public function __construct(
          public readonly string $category,
        public readonly int $colorId,
        public readonly string $stockTrackingType,
        public readonly float $minimumStock,
        public readonly ?float $quantity = null,
        public readonly ?string $unitType = null,
        public readonly ?float $unitCost = null,
        public readonly ?string $notes = null,
        public readonly array $units = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            category: $data['category'],
            colorId: (int) $data['color_id'],
            stockTrackingType: $data['stock_tracking_type'],
            minimumStock: (float) $data['minimum_stock'],
            quantity: isset($data['quantity'])
                ? (float) $data['quantity']
                : null,
            unitType: $data['unit_type'] ?? null,
            unitCost: isset($data['unit_cost'])
                ? (float) $data['unit_cost']
                : null,
            notes: $data['notes'] ?? null,
            units: $data['units'] ?? [],
        );
    }
}