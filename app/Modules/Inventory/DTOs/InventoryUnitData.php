<?php

namespace App\Modules\Inventory\DTOs;

final readonly class InventoryUnitData
{
    public function __construct(
        public string $unitType,
        public string $unitCode,
        public float $quantity,
        public float $unitCost,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            unitType: $data['unit_type'],
            unitCode: trim($data['unit_code']),
            quantity: (float) $data['quantity'],
            unitCost: (float) $data['unit_cost'],
            notes: isset($data['notes'])
                ? trim($data['notes'])
                : null,
        );
    }

    public function totalCost(): float
    {
        return round(
            $this->quantity * $this->unitCost,
            4
        );
    }
}