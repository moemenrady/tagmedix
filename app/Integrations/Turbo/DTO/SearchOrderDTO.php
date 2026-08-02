<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\DTO;

final readonly class SearchOrderDTO
{
    public function __construct(
        public ?string $remoteOrderId = null,
        public ?string $trackingNumber = null,
        public ?string $receiver = null,
        public ?string $phone = null,
        public ?string $status = null,
    ) {
    }
}
