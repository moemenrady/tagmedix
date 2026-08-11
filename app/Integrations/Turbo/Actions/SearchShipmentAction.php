<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\DTO\SearchOrderDTO;
use App\Integrations\Turbo\Services\TurboService;

final class SearchShipmentAction
{
    public function __construct(private readonly TurboService $service)
    {
    }

    public function execute(SearchOrderDTO $dto): array
    {
        return $this->service->search($dto);
    }
}
