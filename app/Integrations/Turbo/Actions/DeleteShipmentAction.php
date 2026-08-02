<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;

final class DeleteShipmentAction
{
    public function __construct(private readonly ShipmentRepositoryInterface $repository)
    {
    }

    public function execute(string $remoteOrderId): bool
    {
        $shipment = $this->repository->findByRemoteOrderId($remoteOrderId);

        if ($shipment === null) {
            return false;
        }

        return $this->repository->delete($shipment->id);
    }
}
