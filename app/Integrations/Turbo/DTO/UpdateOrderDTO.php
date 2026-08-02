<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\DTO;

use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;

final readonly class UpdateOrderDTO
{
    public function __construct(
        public string $remoteOrderId,
        public ?string $receiver = null,
        public ?string $phone = null,
        public ?string $government = null,
        public ?string $area = null,
        public ?string $address = null,
        public ?float $amount = null,
        public ?float $weight = null,
        public ?string $notes = null,
        public ?DeliveryType $deliveryType = null,
        public ?bool $isOrder = null,
        public ?bool $isFragile = null,
        public ?ShipmentStatus $status = null,
    ) {
    }
}
