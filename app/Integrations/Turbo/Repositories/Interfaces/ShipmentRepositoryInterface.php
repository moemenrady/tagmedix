<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Repositories\Interfaces;

use App\Integrations\Turbo\Models\Shipment;

interface ShipmentRepositoryInterface
{
    public function create(array $data): Shipment;

    public function update(int $id, array $data): Shipment;

    public function delete(int $id): bool;

    public function findByRemoteOrderId(string $remoteOrderId): ?Shipment;

    public function findById(int $id): ?Shipment;
}
