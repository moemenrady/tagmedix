<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\Services\TurboService;

final class GetStatusAction
{
    public function __construct(private readonly TurboService $service)
    {
    }

    public function execute(string $remoteOrderId): array
    {
        return $this->service->getStatus($remoteOrderId);
    }
}
