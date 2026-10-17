<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\Services\TurboService;

final class GetGovernmentsAction
{
    public function __construct(private readonly TurboService $service)
    {
    }

    public function execute(): array
    {
        return $this->service->getGovernments();
    }
}
