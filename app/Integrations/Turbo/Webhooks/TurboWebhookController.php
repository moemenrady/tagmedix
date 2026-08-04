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

        $remoteOrderId = (string) ($payload['remote_order_id'] ?? $payload['order_number'] ?? $payload['order_id'] ?? $payload['id'] ?? '');

        if ($remoteOrderId === '') {
            return Response::json(['success' => false, 'message' => 'Missing remote order id or order number.'], 422);
        }

        // Locate shipment by remote_order_id, turbo_id, tracking_number, or id
        $shipment = Shipment::where('remote_order_id', $remoteOrderId)
            ->orWhere('turbo_id', $remoteOrderId)
            ->orWhere('tracking_number', $remoteOrderId)
            ->orWhere('id', $remoteOrderId)
            ->first();

        // If shipment not found, check if an Order exists with this order_number
        if ($shipment === null) {
            $order = Order::where('order_number', $remoteOrderId)->orWhere('id', $remoteOrderId)->first();

            if ($order) {
                $shipment = Shipment::create([
                    'turbo_id' => $payload['id'] ?? ('TURBO-' . $order->id),
                    'remote_order_id' => $order->order_number,
                    'tracking_number' => $payload['tracking_number'] ?? ('TRK-' . $order->order_number),
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

        // Also update local Order status if matching order exists
        $order = Order::where('order_number', $shipment->remote_order_id)->first();
        if ($order) {
            if (in_array(strtolower($statusText), ['delivered', 'تم التسليم', 'المقبوله', '3'])) {
                $order->update(['status' => 'delivered']);
            } elseif (in_array(strtolower($statusText), ['in_transit', 'shipped', 'جاري التوصيل', '2'])) {
                $order->update(['status' => 'shipped']);
            } elseif (in_array(strtolower($statusText), ['canceled', 'ملغي', 'إلغاء'])) {
                $order->update(['status' => 'canceled']);
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
