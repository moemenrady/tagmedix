<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\Services\TurboService;

final class GetAreasAction
{
    public function __construct(private readonly TurboService $service)
    {
    }

    public function execute(string $government): array
    {
        return $this->service->getAreas($government);
    }
}
