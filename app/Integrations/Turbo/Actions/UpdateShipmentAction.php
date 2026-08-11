<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\DTO\UpdateOrderDTO;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;

final class UpdateShipmentAction
{
    public function __construct(private readonly ShipmentRepositoryInterface $repository)
    {
    }

    public function execute(UpdateOrderDTO $dto): Shipment
    {
        $shipment = $this->repository->findByRemoteOrderId($dto->remoteOrderId);

        if ($shipment === null) {
            throw new \RuntimeException('Shipment not found.');
        }

        $this->repository->update($shipment->id, [
            'receiver' => $dto->receiver ?? $shipment->receiver,
            'phone' => $dto->phone ?? $shipment->phone,
            'government' => $dto->government ?? $shipment->government,
            'area' => $dto->area ?? $shipment->area,
            'address' => $dto->address ?? $shipment->address,
            'amount' => $dto->amount ?? $shipment->amount,
            'weight' => $dto->weight ?? $shipment->weight,
            'notes' => $dto->notes ?? $shipment->notes,
            'delivery_type' => $dto->deliveryType?->value ?? $shipment->delivery_type,
            'is_order' => $dto->isOrder ?? $shipment->is_order,
            'is_fragile' => $dto->isFragile ?? $shipment->is_fragile,
            'status' => $dto->status?->value ?? $shipment->status,
            'status_text' => $dto->status?->value ?? $shipment->status_text,
            'internal_status' => $dto->status?->value ?? $shipment->internal_status,
        ]);

        return $shipment->fresh();
    }
}
