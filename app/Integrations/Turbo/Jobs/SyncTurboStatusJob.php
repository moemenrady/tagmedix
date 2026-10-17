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

final class SyncTurboStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ?int $shipmentId = null)
    {
    }

    public function handle(TurboService $service): void
    {
        $query = Shipment::query();

        if ($this->shipmentId !== null) {
            $query->whereKey($this->shipmentId);
        }

        foreach ($query->get() as $shipment) {
            $response = $service->getStatus((string) $shipment->remote_order_id);
            $shipment->forceFill([
                'status' => $response['status'] ?? $shipment->status,
                'status_text' => $response['status_text'] ?? $shipment->status_text,
                'response' => $response,
            ])->save();

            // Synchronize associated Order safely
            $order = $shipment->order ?: ($shipment->remote_order_id ? \App\Models\Order::where('order_number', $shipment->remote_order_id)->first() : null);
            if ($order) {
                if (!$shipment->order_id) {
                    $shipment->forceFill(['order_id' => $order->id])->saveQuietly();
                }

                $mappedStatus = \App\Models\Order::mapTurboStatus($response['status'] ?? null, $response['status_text'] ?? null);
                if ($mappedStatus) {
                    if ($mappedStatus === \App\Models\Order::STATUS_SHIPPED) {
                        if (!in_array($order->status, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_CANCELED])) {
                            $order->update(['status' => \App\Models\Order::STATUS_SHIPPED]);
                        }
                    } else {
                        $order->update(['status' => $mappedStatus]);
                    }
                }
            }
        }
    }
}
