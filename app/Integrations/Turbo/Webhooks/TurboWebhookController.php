<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Webhooks;

use App\Integrations\Turbo\Events\WebhookReceived;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

final class TurboWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Turbo Webhook Received', $payload);

        Event::dispatch(new WebhookReceived($payload));

        $remoteOrderId = (string) ($payload['remote_order_id'] ?? $payload['order_number'] ?? $payload['order_id'] ?? '');
        $turboId = (string) ($payload['id'] ?? $payload['code'] ?? '');
        $trackingNumber = (string) ($payload['tracking_number'] ?? '');

        if ($remoteOrderId === '' && $turboId === '' && $trackingNumber === '') {
            return Response::json(['success' => false, 'message' => 'Missing remote order id, tracking number, or turbo id.'], 422);
        }

        // Locate shipment safely by remote_order_id, turbo_id, or tracking_number (NEVER local auto-increment id)
        $shipment = Shipment::query()
            ->when($remoteOrderId !== '', fn($q) => $q->where('remote_order_id', $remoteOrderId))
            ->when($remoteOrderId === '' && $turboId !== '', fn($q) => $q->where('turbo_id', $turboId))
            ->when($remoteOrderId === '' && $trackingNumber !== '', fn($q) => $q->orWhere('tracking_number', $trackingNumber))
            ->first();

        // If shipment not found, check if an Order exists with this order_number
        if ($shipment === null && $remoteOrderId !== '') {
            $order = Order::where('order_number', $remoteOrderId)->first();

            if ($order) {
                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'turbo_id' => $turboId ?: ('TURBO-' . $order->id),
                    'remote_order_id' => $order->order_number,
                    'tracking_number' => $trackingNumber ?: ('TRK-' . $order->order_number),
                    'receiver' => $order->customer_name,
                    'phone' => $order->phone_1,
                    'phone2' => $order->phone_2,
                    'government' => $order->governorate,
                    'address' => $order->full_address,
                    'amount' => $payload['order_price'] ?? $order->total_price,
                    'status' => $payload['status_text'] ?? ($payload['status'] ?? 'PENDING'),
                    'status_text' => $payload['status_text'] ?? 'قيد المتابعة',
                    'notes' => $payload['delay_reason'] ?? $payload['return_reason'] ?? null,
                    'response' => $payload,
                ]);
            }
        }

        if ($shipment === null) {
            return Response::json(['success' => false, 'message' => 'Shipment or Order not found.'], 404);
        }

        $oldStatus = $shipment->status;
        $statusText = (string) ($payload['status_text'] ?? $payload['status'] ?? $shipment->status);
        $newStatus = (string) ($payload['status'] ?? $statusText);

        $existingResponse = is_array($shipment->response) ? $shipment->response : [];
        $mergedResponse = array_merge($existingResponse, $payload, [
            'captain_name' => $payload['captain_name'] ?? ($existingResponse['captain_name'] ?? null),
            'captain_number1' => $payload['captain_number1'] ?? ($existingResponse['captain_number1'] ?? null),
            'delay_reason' => $payload['delay_reason'] ?? ($existingResponse['delay_reason'] ?? null),
            'branch_name' => $payload['branch_name'] ?? ($existingResponse['branch_name'] ?? null),
        ]);

        $shipment->forceFill([
            'status' => $newStatus,
            'status_text' => $statusText,
            'internal_status' => $payload['internal_status'] ?? $shipment->internal_status,
            'amount' => isset($payload['order_price']) ? (float) $payload['order_price'] : $shipment->amount,
            'notes' => $payload['delay_reason'] ?? $payload['return_reason'] ?? $shipment->notes,
            'response' => $mergedResponse,
        ])->save();

        // Also update local Order status if matching order exists using centralized mapper
        $order = $shipment->order ?: ($shipment->remote_order_id ? Order::where('order_number', $shipment->remote_order_id)->first() : null);
        if ($order) {
            if (!$shipment->order_id) {
                $shipment->forceFill(['order_id' => $order->id])->saveQuietly();
            }

            $mappedStatus = Order::mapTurboStatus($payload['status'] ?? null, $statusText);
            if ($mappedStatus) {
                // If the mapped status is shipped, only update if not already delivered or canceled
                if ($mappedStatus === Order::STATUS_SHIPPED) {
                    if (!in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_CANCELED])) {
                        $order->update(['status' => Order::STATUS_SHIPPED]);
                    }
                } else {
                    $order->update(['status' => $mappedStatus]);
                }
            }
        }

        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'response' => $payload,
        ]);

        return Response::json([
            'success' => true,
            'message' => 'Webhook processed successfully',
        ]);
    }
}
