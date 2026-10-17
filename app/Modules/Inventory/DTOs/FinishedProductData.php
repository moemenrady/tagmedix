<?php

namespace App\Modules\Inventory\DTOs;

final readonly class FinishedProductData
{
    public function __construct(
        public int $pantTypeId,
        public int $typeId,

        public ?int $styleId,
        public int $colorId,
        public ?int $sizeId,
        public ?int $sleeveTypeId,

        public float $quantity,
        public float $unitCost,

        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pantTypeId: (int) $data['pant_type_id'],
            typeId: (int) $data['type_id'],

            styleId: isset($data['style_id'])
                ? (int) $data['style_id']
                : null,

            colorId: (int) $data['color_id'],

            sizeId: isset($data['size_id'])
                ? (int) $data['size_id']
                : null,

            sleeveTypeId: isset($data['sleeve_type_id'])
                ? (int) $data['sleeve_type_id']
                : null,

            quantity: (float) $data['quantity'],

            unitCost: (float) $data['price'],

            notes: isset($data['notes'])
                ? trim($data['notes'])
                : null,
        );
    }

    public function totalCost(): float
    {
        return $this->quantity * $this->unitCost;
    }
}