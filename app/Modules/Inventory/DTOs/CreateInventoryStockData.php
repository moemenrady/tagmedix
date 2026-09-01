<?php

namespace App\Modules\Inventory\DTOs;

final readonly class CreateInventoryStockData
{
    /**
     * @param InventoryUnitData[] $units
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

        public ?int $createdBy = null,
    ) {}

    public static function fromArray(
        array $data,
        ?int $userId = null
    ): self {
        $units = [];

        foreach ($data['units'] ?? [] as $unit) {
            $units[] = InventoryUnitData::fromArray($unit);
        }

        return new self(
            name: trim($data['name']),
            category: $data['category'],
            colorId: (int) $data['color_id'],
            minimumStock: (float) $data['minimum_stock'],
            stockTrackingType: $data['stock_tracking_type'],

            unitType: $data['unit_type'] ?? null,
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

            createdBy: $userId,
        );
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