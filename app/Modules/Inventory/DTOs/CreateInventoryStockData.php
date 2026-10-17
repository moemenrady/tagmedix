<?php

namespace App\Modules\Inventory\DTOs;

use App\Modules\Inventory\DTOs\FinishedProductData;

final readonly class CreateInventoryStockData
{
    /**
     * @param InventoryUnitData[] $units
     * @param FinishedProductData[] $finishedProducts
     */
    public function __construct(
        public string $name,
        public string $category,
        public int $colorId,
        public float $minimumStock,
        public string $stockTrackingType,

        public ?string $unitType = null,
        public ?float $quantity = null,
        public ?float $unitCost = null,
        public ?string $notes = null,

        public array $units = [],
        public array $finishedProducts = [],
        public ?int $existingItemId = null,
        public ?int $createdBy = null,
    ) {}

    public static function fromArray(
        array $data,
        ?int $userId = null
    ): self {
        $units = [];
        $finishedProducts = [];

        foreach ($data['items'] ?? [] as $product) {
            $finishedProducts[] = FinishedProductData::fromArray($product);
        }
        foreach ($data['units'] ?? [] as $unit) {
            $units[] = InventoryUnitData::fromArray($unit);
        }

        return new self(
            name: trim($data['name']),
            category: $data['category'],
            colorId: (int) $data['color_id'],
            minimumStock: (float) $data['minimum_stock'],
            stockTrackingType: $data['stock_tracking_type'],

            unitType: $data['category'] === 'finished_product'
                ? 'piece'
                : ($data['unit_type'] ?? null),

            quantity: isset($data['quantity'])
                ? (float) $data['quantity']
                : null,

            unitCost: isset($data['unit_cost'])
                ? (float) $data['unit_cost']
                : null,

            notes: isset($data['notes'])
                ? trim($data['notes'])
                : null,

            units: $units,
            finishedProducts: $finishedProducts,
            existingItemId: isset($data['existing_item_id'])
                ? (int) $data['existing_item_id']
                : null,
            createdBy: $userId,
        );
    }
    public function isFinishedProduct(): bool
    {
        return $this->category === 'finished_product';
    }
    public function isBulk(): bool
    {
        return $this->stockTrackingType === 'bulk';
    }

    public function isIndividual(): bool
    {
        return $this->stockTrackingType === 'individual';
    }
}
