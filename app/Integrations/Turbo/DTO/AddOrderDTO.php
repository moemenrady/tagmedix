<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\DTO;

use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;

final readonly class AddOrderDTO
{
    public function __construct(
        public string $remoteOrderId,
        public string $receiver,
        public string $phone,
        public string $government,
        public string $area,
        public string $address,
        public float $amount,
        public float $weight,
        public ?string $notes,
        public DeliveryType $deliveryType,
        public bool $isOrder,
        public bool $isFragile,
        public ShipmentStatus $status,
    ) {
    }
}
