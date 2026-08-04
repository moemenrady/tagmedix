<?php

namespace App\Http\Controllers;

use App\Integrations\Turbo\DTO\AddOrderDTO;
use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Services\TurboService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TurboOperationsController extends Controller
{
    public function index()
    {
        return view('pages.turbo.index');
    }

    public function data()
    {
        $shipments = Shipment::latest()->get();

        $turboData = $shipments->map(function ($shipment) {
            $resp = is_array($shipment->response) ? $shipment->response : [];

            $statusId = 1;
            $statusLower = strtolower((string) $shipment->status);
            if (in_array($statusLower, ['delivered', 'completed', '3', 'تم التسليم', 'المقبوله'])) {
                $statusId = 3;
            } elseif (in_array($statusLower, ['in_transit', 'shipped', 'accepted', 'in-progress', '2', 'جاري التوصيل'])) {
                $statusId = 2;
            } elseif (in_array($statusLower, ['canceled', 'cancelled', 'ملغي', '4'])) {
                $statusId = 4;
            }

            return [
                'id' => $shipment->id,
                'turbo_id' => $shipment->turbo_id,
                'category_id' => 1,
                'status_id' => $statusId,
                'status_name' => $shipment->status_text ?? ($shipment->status ?? 'قيد المتابعة'),
                'order_code' => $shipment->remote_order_id ?? ('ORD-' . $shipment->id),
                'title' => 'شحنة العميل: ' . ($shipment->receiver ?? 'عميل'),
                'content' => $shipment->notes ?? ($resp['delay_reason'] ?? 'شحنة طلبات ملابس طبية من Tagmedix'),
                'receiver' => $shipment->receiver,
                'phone' => $shipment->phone,
                'phone2' => $shipment->phone2,
                'government' => $shipment->government,
                'area' => $shipment->area,
                'address' => $shipment->address,
                'amount' => (float) $shipment->amount,
                'weight' => (float) $shipment->weight,
                'tracking_number' => $shipment->tracking_number ?? ('TRK-' . $shipment->id),
                'delivery_type' => $shipment->delivery_type ?? 'express',
                'captain_name' => $shipment->captain_name ?? $resp['captain_name'] ?? null,
                'captain_phone' => $shipment->captain_phone ?? $resp['captain_number1'] ?? null,
                'delay_reason' => $shipment->delay_reason ?? $resp['delay_reason'] ?? $shipment->notes,
                'branch_name' => $resp['branch_name'] ?? null,
                'updated_at_human' => $shipment->updated_at ? $shipment->updated_at->diffForHumans() : 'الآن',
                'created_at' => $shipment->created_at ? $shipment->created_at->format('Y-m-d h:i A') : now()->format('Y-m-d h:i A'),
            ];
        })->toArray();

        // Get orders ready for shipping (unshipped orders)
        $shippedOrderNumbers = Shipment::pluck('remote_order_id')->filter()->toArray();
        $unshippedOrders = Order::with(['customer', 'address'])
            ->whereNotIn('order_number', $shippedOrderNumbers)
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($ord) {
                return [
                    'id' => $ord->id,
                    'order_number' => $ord->order_number,
                    'customer_name' => $ord->customer?->name ?? '',
                    'phone_1' => $ord->customer?->phone_1 ?? '',
                    'governorate' => $ord->address?->governorate ?? '',
                    'full_address' => $ord->address?->full_address ?? '',
                    'total_price' => $ord->total_price,
                    'status' => $ord->status,
                ];
            });

        return response()->json([
            'turbo' => $turboData,
            'unshipped_orders' => $unshippedOrders,
            'categories' => [
                ['id' => 1, 'name' => 'مشاكل الشحن والتوصيل'],
                ['id' => 2, 'name' => 'استفسار عن شحنة'],
                ['id' => 3, 'name' => 'مرتجعات ومفقودات'],
            ],
            'statuses' => [
                ['id' => 1, 'name' => 'قيد الانتظار (Pending)'],
                ['id' => 2, 'name' => 'جاري التوصيل مع Turbo (In Transit)'],
                ['id' => 3, 'name' => 'تم التسليم بنجاح (Delivered)'],
                ['id' => 4, 'name' => 'ملغي / مرتجع (Canceled)'],
            ]
        ]);
    }

    /**
     * Dispatch an existing Order from system to Turbo Gateway
     */
    public function sendOrderToTurbo(Request $request, TurboService $turboService)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'weight' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $order = Order::with(['customer', 'address'])->findOrFail($validated['order_id']);

        $custName = $order->customer?->name ?? '';
        $phone1 = $order->customer?->phone_1 ?? '';
        $phone2 = $order->customer?->phone_2 ?? '';
        $gov = $order->address?->governorate ?? '';
        $addr = $order->address?->full_address ?? '';

        // Check if shipment already exists for this order
        $existingShipment = Shipment::where('remote_order_id', $order->order_number)->first();
        if ($existingShipment) {
            return response()->json([
                'success' => false, 
                'message' => 'هذا الطلب مضاف مسبقاً في شحنات Turbo برقم تتبع ' . $existingShipment->tracking_number
            ], 422);
        }

        try {
            $dto = new AddOrderDTO(
                remoteOrderId: (string) $order->order_number,
                receiver: (string) $custName,
                phone: (string) $phone1,
                government: (string) $gov,
                area: 'المنطقة المركزية',
                address: (string) $addr,
                amount: (float) $order->total_price,
                weight: (float) ($request->input('weight') ?? 1.0),
                notes: $validated['notes'] ?? 'شحنة ملابس طبية Tagmedix',
                deliveryType: DeliveryType::EXPRESS,
                isOrder: true,
                isFragile: false,
                status: ShipmentStatus::PENDING
            );

            $apiResult = $turboService->addOrder($dto);
            $turboId = $apiResult['id'] ?? $apiResult['remote_order_id'] ?? null;
            $trackingNumber = $apiResult['tracking_number'] ?? null;

            $shipment = Shipment::create([
                'turbo_id' => $turboId,
                'remote_order_id' => $order->order_number,
                'tracking_number' => $trackingNumber,
                'receiver' => $custName,
                'phone' => $phone1,
                'phone2' => $phone2,
                'government' => $gov,
                'area' => 'المنطقة المركزية',
                'address' => $addr,
                'amount' => $order->total_price,
                'weight' => $request->input('weight', 1.0),
                'notes' => $validated['notes'] ?? 'شحنة ملابس طبية Tagmedix',
                'status' => 'PENDING',
                'status_text' => 'تم إرسال الطلب إلى Turbo بنجاح',
                'delivery_type' => 'express',
                'is_order' => true,
                'response' => $apiResult,
            ]);

            $order->update(['status' => 'shipped']);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الطلب ' . $order->order_number . ' إلى شركة Turbo بنجاح!',
                'data' => $shipment
            ]);
        } catch (\Throwable $e) {
            Log::error('Turbo API Add Order Failure: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال الطلب إلى Turbo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * GET /turbo/areas/{government}
     */
    public function getAreas(Request $request, string $government, TurboService $turboService)
    {
        try {
            $res = $turboService->getAreas($government);
            return response()->json(['success' => true, 'data' => $res]);
        } catch (\Throwable $e) {
            Log::error('Turbo API Areas Fetch Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'تعذر جلب المناطق من Turbo'
            ], 400);
        }
    }

    /**
     * POST /turbo/delete-order
     */
    public function deleteOrder(Request $request, TurboService $turboService)
    {
        $orderId = $request->input('remote_order_id')
            ?? $request->input('search_key')
            ?? $request->input('id');

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'الرجاء تحديد رقم الطلب'], 422);
        }

        try {
            $turboService->deleteOrder((string) $orderId);

            Shipment::where('remote_order_id', $orderId)
                ->orWhere('id', $orderId)
                ->orWhere('turbo_id', $orderId)
                ->delete();

            return response()->json(['success' => true, 'message' => 'تم حذف الشحنة بنجاح']);
        } catch (\Throwable $e) {
            Log::error('Turbo API Delete Order Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف الشحنة من Turbo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /turbo/resend-request
     */
    public function resendRequest(Request $request, TurboService $turboService)
    {
        $remoteOrderId = $request->input('remote_order_id') ?? $request->input('id');

        if (!$remoteOrderId) {
            return response()->json(['success' => false, 'message' => 'الرجاء تحديد معرف الشحنة'], 422);
        }

        try {
            $turboService->resend((string) $remoteOrderId);

            Shipment::where('remote_order_id', $remoteOrderId)
                ->orWhere('id', $remoteOrderId)
                ->orWhere('turbo_id', $remoteOrderId)
                ->update([
                    'status' => 'IN_TRANSIT',
                    'status_text' => 'تم إعادة إرسال طلب التوصيل لـ Turbo بنجاح',
                ]);

            return response()->json(['success' => true, 'message' => 'تم إعادة إرسال طلب التوصيل لـ Turbo بنجاح']);
        } catch (\Throwable $e) {
            Log::error('Turbo API Resend Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إعادة الإرسال لـ Turbo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /turbo/cancel-request
     */
    public function cancelRequest(Request $request, TurboService $turboService)
    {
        $shipmentId = $request->input('id'); // Internal ID or Turbo ID
        $remoteOrderId = $request->input('remote_order_id');

        $shipment = Shipment::where('id', $shipmentId)
            ->orWhere('turbo_id', $shipmentId)
            ->orWhere('remote_order_id', $remoteOrderId)
            ->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على الشحنة في النظام'], 404);
        }

        // Must send Turbo's shipment ID (id) to Turbo cancel API endpoint
        $turboShipmentId = $shipment->turbo_id ?? $shipment->remote_order_id;

        try {
            $turboService->cancel($turboShipmentId);

            $shipment->update([
                'status' => 'CANCELED',
                'status_text' => 'تم إلغاء الشحنة بنجاح',
            ]);

            // Update corresponding Order Status if exists
            Order::where('order_number', $shipment->remote_order_id)->update(['status' => 'cancelled']);

            return response()->json(['success' => true, 'message' => 'تم إلغاء الشحنة بنجاح عبر Turbo']);
        } catch (\Throwable $e) {
            Log::error('Turbo API Cancel Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء الشحنة من Turbo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Webhook Handler for Turbo Automated Updates
     * POST /api/turbo/v1/update-order
     */
    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('Turbo Webhook received:', $data);

        $remoteOrderId = $data['order_number'] ?? $data['remote_order_id'] ?? null;

        if (!$remoteOrderId) {
            return response()->json(['success' => false, 'message' => 'Order identifier missing'], 400);
        }

        $shipment = Shipment::where('remote_order_id', $remoteOrderId)
            ->orWhere('turbo_id', $remoteOrderId)
            ->first();

        if ($shipment) {
            $shipment->update([
                'status' => $data['status'] ?? $shipment->status,
                'status_text' => $data['status_text'] ?? $shipment->status_text,
                'delay_reason' => $data['delay_reason'] ?? null,
                'captain_name' => $data['captain_name'] ?? null,
                'captain_phone' => $data['captain_number1'] ?? null,
                'response' => array_merge(is_array($shipment->response) ? $shipment->response : [], $data),
            ]);

            // Synchronize with local Order status
            if (isset($data['status'])) {
                $statusMap = [
                    2 => 'in_transit',
                    3 => 'delivered',
                    4 => 'cancelled',
                ];
                if (isset($statusMap[$data['status']])) {
                    Order::where('order_number', $shipment->remote_order_id)
                        ->update(['status' => $statusMap[$data['status']]]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook updated successfully']);
    }
}