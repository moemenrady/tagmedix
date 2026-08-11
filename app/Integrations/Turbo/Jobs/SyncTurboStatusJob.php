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
        }
    }
}
