<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Webhooks;

use App\Integrations\Turbo\Events\WebhookReceived;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;

final class TurboWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        Event::dispatch(new WebhookReceived($payload));

        $remoteOrderId = (string) ($payload['remote_order_id'] ?? $payload['order_id'] ?? '');

        if ($remoteOrderId === '') {
            return Response::json(['success' => false, 'message' => 'Missing remote order id.'], 422);
        }

        $shipment = Shipment::where('remote_order_id', $remoteOrderId)->first();

        if ($shipment === null) {
            return Response::json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $oldStatus = $shipment->status;
        $newStatus = (string) ($payload['status'] ?? $payload['status_text'] ?? $shipment->status);

        $shipment->forceFill([
            'status' => $newStatus,
            'status_text' => $newStatus,
            'response' => $payload,
        ])->save();

        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'response' => $payload,
        ]);

        return Response::json(['success' => true]);
    }
}
