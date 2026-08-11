<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Repositories\Eloquent;

use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;

final class ShipmentRepository implements ShipmentRepositoryInterface
{
    public function create(array $data): Shipment
    {
        return Shipment::create($data);
    }

    public function update(int $id, array $data): Shipment
    {
        $shipment = $this->findById($id);

        if ($shipment === null) {
            throw new \RuntimeException('Shipment not found.');
        }

        $shipment->fill($data);
        $shipment->save();

        return $shipment;
    }

    public function delete(int $id): bool
    {
        return (bool) Shipment::destroy($id);
    }

    public function findByRemoteOrderId(string $remoteOrderId): ?Shipment
    {
        return Shipment::where('remote_order_id', $remoteOrderId)->first();
    }

    public function findById(int $id): ?Shipment
    {
        return Shipment::find($id);
    }
}
