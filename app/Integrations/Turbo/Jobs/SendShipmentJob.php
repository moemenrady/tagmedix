<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Jobs;

use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $shipmentId)
    {
    }

    public function handle(TurboService $service): void
    {
        $shipment = Shipment::find($this->shipmentId);

        if ($shipment === null) {
            return;
        }

        $service->addOrder(new \App\Integrations\Turbo\DTO\AddOrderDTO(
            remoteOrderId: (string) $shipment->remote_order_id,
            receiver: (string) $shipment->receiver,
            phone: (string) $shipment->phone,
            government: (string) $shipment->government,
            area: (string) $shipment->area,
            address: (string) $shipment->address,
            amount: (float) $shipment->amount,
            weight: (float) $shipment->weight,
            notes: $shipment->notes,
            deliveryType: \App\Integrations\Turbo\Enums\DeliveryType::from($shipment->delivery_type),
            isOrder: (bool) $shipment->is_order,
            isFragile: (bool) $shipment->is_fragile,
            status: \App\Integrations\Turbo\Enums\ShipmentStatus::from($shipment->status),
        ));
    }
}
