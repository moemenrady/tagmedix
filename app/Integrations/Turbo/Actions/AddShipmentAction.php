<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Actions;

use App\Integrations\Turbo\DTO\AddOrderDTO;
use App\Integrations\Turbo\Jobs\SendShipmentJob;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;
use Illuminate\Support\Facades\Bus;

final class AddShipmentAction
{
    public function __construct(private readonly ShipmentRepositoryInterface $repository)
    {
    }

    public function execute(AddOrderDTO $dto): Shipment
    {
        $shipment = $this->repository->create([
            'remote_order_id' => $dto->remoteOrderId,
            'receiver' => $dto->receiver,
            'phone' => $dto->phone,
            'government' => $dto->government,
            'area' => $dto->area,
            'address' => $dto->address,
            'amount' => $dto->amount,
            'weight' => $dto->weight,
            'notes' => $dto->notes,
            'delivery_type' => $dto->deliveryType->value,
            'is_order' => $dto->isOrder,
            'is_fragile' => $dto->isFragile,
            'status' => $dto->status->value,
            'status_text' => $dto->status->value,
            'internal_status' => $dto->status->value,
        ]);

        if ($shipment->getKey() !== null) {
            Bus::dispatch(new SendShipmentJob((int) $shipment->getKey()));
        }

        return $shipment;
    }
}
