<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Turbo\TurboApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use App\Models\Area;
use App\Models\Government;

class FinishedGoodController extends Controller
{
    protected TurboApiService $turboApiService;

    public function __construct(TurboApiService $turboApiService)
    {
        $this->turboApiService = $turboApiService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $orders = Order::query()
            ->with(['customer', 'address', 'items.style', 'items.type', 'items.color', 'items.size', 'items.pantType', 'payments'])
            ->withSum('payments', 'amount')
            ->where(function ($q) {
                $q->where('status', 'finished-goods')
                    ->orWhere('status', 'ready-to-ship');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('phone_1', 'LIKE', "%{$search}%")
                                ->orWhere('phone_2', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_value' => $orders->sum('total_price'),
            'total_remaining' => $orders->sum(fn($order) => $order->getRemainingAmount()),
        ];

        $returnAmounts = \App\Models\TurboReturnAmount::all();

        return view('pages.finished-goods.index', compact('orders', 'stats', 'search', 'returnAmounts'));
    }

    public function indexShipment(Request $request)
    {
        $query = Shipment::with([
            'order:id,order_number,status',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        if ($request->filled('q')) {

            $q = trim($request->q);

            // If searched code is not in local DB, attempt live lookup from Turbo API
            if (! Shipment::where('turbo_id', $q)->orWhere('tracking_number', $q)->orWhere('remote_order_id', $q)->exists()) {
                if (is_numeric($q) || str_starts_with($q, 'TRK-') || str_starts_with($q, 'ORD-')) {
                    try {
                        $searchRes = $this->turboApiService->searchOrder($q);
                        $foundItems = $searchRes['result'] ?? $searchRes['data'] ?? [];
                        if (is_array($foundItems) && ! empty($foundItems[0])) {
                            $item = $foundItems[0];
                            $code = (string) ($item['code'] ?? $q);
                            $remoteOrd = (string) ($item['invoice_number'] ?? $item['remote_order_id'] ?? '');

                            $order = $remoteOrd ? Order::where('order_number', $remoteOrd)->first() : null;

                            $newShipment = Shipment::create([
                                'order_id' => $order?->id,
                                'turbo_id' => $code,
                                'remote_order_id' => $remoteOrd ?: ('TURBO-' . $code),
                                'tracking_number' => $code,
                                'receiver' => $order?->customer_name ?: 'عميل Turbo',
                                'phone' => $order?->customer_phone_1 ?: '',
                                'government' => $item['expected_branch'] ?? $item['branch'] ?? '',
                                'area' => $item['branch'] ?? '',
                                'amount' => $order?->total_price ?? (float) ($item['order_price'] ?? 0),
                                'status' => $item['status'] ?? 'pending',
                                'status_text' => $item['status'] ?? 'قيد المتابعة',
                                'last_synced_at' => now(),
                                'response' => $item,
                            ]);

                            $this->turboApiService->applyShipmentUpdate($newShipment, $item, 'api_search_import');
                        }
                    } catch (\Throwable $e) {
                        Log::info("Turbo live search lookup exception for {$q}: " . $e->getMessage());
                    }
                }
            }

            $query->where(function ($query) use ($q) {

                $query
                    ->where('turbo_id', 'like', "%{$q}%")
                    ->orWhere('remote_order_id', 'like', "%{$q}%")
                    ->orWhere('tracking_number', 'like', "%{$q}%")
                    ->orWhere('receiver', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('phone2', 'like', "%{$q}%")
                    ->orWhere('invoice_number', 'like', "%{$q}%")
                    ->orWhere('government', 'like', "%{$q}%")
                    ->orWhere('area', 'like', "%{$q}%")
                    ->orWhereHas('order', function ($orderQuery) use ($q) {

                        $orderQuery->where(
                            'order_number',
                            'like',
                            "%{$q}%"
                        );
                    });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Internal Status
    |--------------------------------------------------------------------------
    */

        if ($request->filled('internal_status')) {

            $query->where(
                'internal_status',
                $request->internal_status
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Government
    |--------------------------------------------------------------------------
    */

        if ($request->filled('government')) {

            $query->where(
                'government',
                $request->government
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Delivery Type
    |--------------------------------------------------------------------------
    */

        if ($request->filled('delivery_type')) {

            $query->where(
                'delivery_type',
                $request->delivery_type
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

        if ($request->filled('from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

        $statisticsQuery = clone $query;

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'total_amount' => (clone $statisticsQuery)->sum('amount'),

            'total_return_amount' => (clone $statisticsQuery)->sum('return_amount'),

            'total_collected_amount' => (clone $statisticsQuery)->sum('collected_amount'),

            'average_amount' => (clone $statisticsQuery)->avg('amount'),

            'average_weight' => (clone $statisticsQuery)->avg('weight'),

            'in_transit_count' => (clone $statisticsQuery)
                ->where(function ($q) {
                    $q->whereIn('status', ['in_transit', 'shipped', '2', 'جاري التوصيل', 'مع المندوب'])
                      ->orWhere('status', 'like', '%جاري%')
                      ->orWhere('status', 'like', '%transit%');
                })->count(),

            'delivered_count' => (clone $statisticsQuery)
                ->where(function ($q) {
                    $q->whereIn('status', ['delivered', 'completed', '3', 'تم التسليم', 'مكتمل'])
                      ->orWhere('status', 'like', '%تسليم%')
                      ->orWhere('status', 'like', '%delivered%');
                })->count(),

            'delayed_count' => (clone $statisticsQuery)
                ->where(function ($q) {
                    $q->where('response', 'like', '%delay%')
                      ->orWhere('notes', 'like', '%تأجيل%')
                      ->orWhere('notes', 'like', '%تأخير%');
                })->count(),

            'returned_count' => (clone $statisticsQuery)
                ->where(function ($q) {
                    $q->whereIn('status', ['returned', 'canceled', 'cancelled', '4', 'مرتجع', 'ملغي', 'محذوفة', 'محذوف', 'deleted'])
                      ->orWhere('status', 'like', '%مرتجع%')
                      ->orWhere('status', 'like', '%ملغي%')
                      ->orWhere('status', 'like', '%محذوف%');
                })->count(),

            'orders' => (clone $statisticsQuery)
                ->where('is_order', true)
                ->count(),

            'fragile' => (clone $statisticsQuery)
                ->where('is_fragile', true)
                ->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Shipments
    |--------------------------------------------------------------------------
    */

        $shipments = $query
            ->with(['statusHistories' => fn($h) => $h->latest()])
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        /*
    |--------------------------------------------------------------------------
    | Filter Data
    |--------------------------------------------------------------------------
    */

        $statuses = Shipment::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $internalStatuses = Shipment::query()
            ->whereNotNull('internal_status')
            ->where('internal_status', '!=', '')
            ->distinct()
            ->orderBy('internal_status')
            ->pluck('internal_status');

        $governments = Shipment::query()
            ->whereNotNull('government')
            ->where('government', '!=', '')
            ->distinct()
            ->orderBy('government')
            ->pluck('government');

        return view(
            'pages.finished-goods.shipments',
            compact(
                'shipments',
                'statistics',
                'statuses',
                'internalStatuses',
                'governments'
            )
        );
    }

    /**
     * Bulk Sync shipments from Turbo API
     */
    public function syncTurboShipments(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->input('from') ?: date('Y-m-d', strtotime('-30 days'));
        $to = $request->input('to') ?: date('Y-m-d', strtotime('+1 day'));

        try {
            $result = $this->turboApiService->syncShipmentsFromApi($from, $to);

            return response()->json([
                'success' => true,
                'message' => "تمت المزامنة بنجاح! تم فحص {$result['total_fetched']} وتحديث {$result['updated_count']} شحنة في النظام.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk Sync Turbo Shipments Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال بـ Turbo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Single Shipment on-demand sync
     */
    public function syncSingleShipment(Request $request, $id)
    {
        $shipment = Shipment::with(['order', 'statusHistories'])->findOrFail($id);

        try {
            $synced = $this->turboApiService->syncSingleShipment($shipment);
            $shipment->refresh();
            $shipment->load(['order', 'statusHistories' => fn($h) => $h->latest()]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'message' => $synced ? 'تم تحديث بيانات الشحنة من Turbo بنجاح' : 'تم التحقق من Turbo، لا توجد تحديثات جديدة للشحنة حالياً',
                'shipment' => $shipment,
            ]);
        } catch (\Throwable $e) {
            Log::error("Single Shipment Sync Error for {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الشحنة من Turbo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dev-only debug endpoint to view raw Turbo API response
     */
    public function debugTurboShipment(Request $request, $id)
    {
        abort_unless(config('app.debug'), 403, 'Debug mode is disabled.');

        $shipment = Shipment::with(['order', 'statusHistories'])->findOrFail($id);
        $searchKey = $shipment->tracking_number ?: ($shipment->turbo_id ?: ($shipment->remote_order_id ?: $shipment->id));

        $liveTurboResponse = null;
        try {
            $liveTurboResponse = $this->turboApiService->searchOrder($searchKey);
        } catch (\Throwable $e) {
            $liveTurboResponse = [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'shipment_id' => $shipment->id,
                'search_key_used' => $searchKey,
                'live_turbo_response' => $liveTurboResponse,
                'stored_shipment_response' => $shipment->response,
                'shipment' => $shipment,
            ]);
        }

        dd([
            '💡 Dev Mode' => 'Direct Turbo API Live Inspection',
            'Shipment ID' => $shipment->id,
            'Search Key Sent to Turbo' => $searchKey,
            'Live Turbo API Response (/external-api/search-order)' => $liveTurboResponse,
            'Stored in Database (shipments.response)' => $shipment->response,
            'Shipment Status' => $shipment->status,
            'Shipment Status Text' => $shipment->status_text,
            'Order' => $shipment->order ? $shipment->order->toArray() : null,
        ]);
    }

    /**
     * Cancel or mark shipment as deleted/canceled
     */
    public function cancelShipment(Request $request, $id)
    {
        $shipment = Shipment::with(['order', 'statusHistories'])->findOrFail($id);
        $oldStatus = (string) $shipment->status;

        // Try informing Turbo API
        $turboMessage = null;
        try {
            $turboId = $shipment->turbo_id ?: $shipment->tracking_number;
            if ($turboId) {
                $cancelRes = $this->turboApiService->cancelOrder($turboId);
                $turboMessage = $cancelRes['message'] ?? 'تم إرسال الإلغاء لـ Turbo';
            }
        } catch (\Throwable $e) {
            // If already deleted on Turbo or 404, we record that gracefully
            Log::info("Shipment {$id} cancellation note from Turbo: " . $e->getMessage());
            $turboMessage = $e->getMessage();
        }

        $now = now();
        $existingResponse = is_array($shipment->response) ? $shipment->response : [];
        $existingResponse['cancellation_note'] = $turboMessage ?: 'تم الإلغاء من النظام';

        $shipment->forceFill([
            'status' => 'محذوفة',
            'status_text' => 'محذوفة',
            'returned_at' => $shipment->returned_at ?: $now,
            'last_synced_at' => $now,
            'response' => $existingResponse,
        ])->save();

        if ($oldStatus !== 'محذوفة') {
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'old_status' => $oldStatus,
                'new_status' => 'محذوفة',
                'response' => [
                    'action' => 'canceled_by_user',
                    'turbo_note' => $turboMessage,
                ],
                'created_at' => $now,
            ]);
        }

        // Sync with primary order
        $order = $shipment->order ?: ($shipment->order_id ? Order::find($shipment->order_id) : null);
        if (! $order && ! empty($shipment->remote_order_id)) {
            $order = Order::where('order_number', $shipment->remote_order_id)->first();
        }

        if ($order && $order->status !== 'canceled') {
            $order->update(['status' => 'canceled']);
        }

        $shipment->refresh();
        $shipment->load(['order', 'statusHistories' => fn($h) => $h->latest()]);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الشحنة ووسمها كمحذوفة بنجاح وتحديث الطلب المرتبط',
            'shipment' => $shipment,
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'address', 'items.style', 'items.type', 'items.color', 'items.size', 'items.pantType', 'payments.creator'])
            ->withSum('payments', 'amount')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
            'remaining_amount' => $order->getRemainingAmount(),
            'total_paid' => $order->getTotalPaid(),
        ]);
    }

    public function shipOrder(Request $request, $id)
    {
        $order = Order::with([
            'customer',
            'address',
            'items',
        ])->findOrFail($id);

        $customerName = $order->customer?->name
            ?? $order->customer_name
            ?? '';

        $phone1 = $order->customer?->phone_1
            ?? $order->phone_1
            ?? '';

        $phone2 = $order->customer?->phone_2
            ?? $order->phone_2
            ?? '';

        $governorate = $order->address?->governorate
            ?? $order->governorate
            ?? '';

        $fullAddress = $order->address?->full_address
            ?? $order->full_address
            ?? '';

        $validated = $request->validate([
            'government'             => 'required|string',
            'area'                   => 'nullable|string',
            'address'                => 'required|string',

            'api_followup_phone'     => 'nullable|string',

            'amount_to_be_collected' => 'nullable|numeric',
            'order_summary'         => 'nullable|string',

            'weight'                => 'nullable|numeric',
            'notes'                 => 'nullable|string',

            'delivery_type'         => 'nullable',

            'remote_order_id'       => 'nullable|string',

            'receiver'              => 'required|string',

            'phone1'                => 'required|string',
            'phone2'                => 'nullable|string',

            'is_order'              => 'nullable',
            'is_fragile'            => 'nullable',

            'return_amount'         => 'required|numeric',
            'return_summary'        => 'nullable|string',

            'remote_shipment_id'    => 'nullable|string',

            'invoice_number'        => 'nullable|string',

            'can_open'              => 'nullable',

            'number_of_items'       => 'nullable|numeric',
        ]);



        $government = Government::where('name', $validated['government'])
            ->first();

        if (! $government) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'government' => 'المحافظة المحددة غير موجودة في قاعدة البيانات.',
            ]);
        }


        $area = null;

        if (! empty($validated['area'])) {

            $area = Area::where('government_id', $government->id)
                ->where('name', $validated['area'])
                ->first();

            if (! $area) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'area' => 'المنطقة المحددة غير موجودة ضمن المحافظة المختارة.',
                ]);
            }
        }


        $governmentRealId = $government->real_id ?: $government->id;

        $areaRealId = $area?->real_id ?: ($area?->id ?: $validated['area']);
        $shipmentPayload = [
            'second_client' => 'Tagmedix',

            /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    | Send Turbo real IDs instead of local IDs / names
    |--------------------------------------------------------------------------
    */

            'government' => $governmentRealId,

            'area' => $areaRealId,

            'address' => $validated['address']
                ?? $fullAddress,

            'api_followup_phone' => $validated['api_followup_phone']
                ?? $phone1,

            'amount_to_be_collected' => isset(
                $validated['amount_to_be_collected']
            )
                ? (float) $validated['amount_to_be_collected']
                : (float) $order->getRemainingAmount(),

            'order_summary' => $validated['order_summary']
                ?? null,

            'weight' => isset($validated['weight'])
                ? (float) $validated['weight']
                : 1,

            'notes' => $validated['notes']
                ?? null,

            'delivery_type' => $validated['delivery_type']
                ?? 0,

            'remote_order_id' => $validated['remote_order_id']
                ?? $order->order_number,

            'receiver' => $validated['receiver']
                ?? $customerName,

            'phone1' => $validated['phone1']
                ?? $phone1,

            'phone2' => $validated['phone2']
                ?? $phone2,

            'is_order' => (string) (
                $validated['is_order'] ?? '0'
            ),

            'is_fragile' => (int) (
                $validated['is_fragile'] ?? 0
            ),

            'return_amount' => isset(
                $validated['return_amount']
            )
                ? (float) $validated['return_amount']
                : 0,

            'remote_shipment_id' =>
            $validated['remote_shipment_id'] ?? null,

            'return_summary' =>
            $validated['return_summary'] ?? null,

            'invoice_number' =>
            $validated['invoice_number']
                ?? $order->order_number,

            'can_open' => isset($validated['can_open'])
                ? (int) $validated['can_open']
                : 1,

            'number_of_items' => isset($validated['number_of_items'])
                ? (int) $validated['number_of_items']
                : $order->items()->count(),
        ];





        $startedAt = microtime(true);

        try {
            // array:22 [▼ // app/Http/Controllers/FinishedGoodController.php:417
            //   "second_client" => "Tagmedix"
            //   "government" => "الإسكندرية"
            //   "area" => "محمور المحمودية من اول الريادة الى شارع الشرطة"
            //   "address" => "test address"
            //   "api_followup_phone" => "01000000000"
            //   "amount_to_be_collected" => 110.0
            //   "order_summary" => "1x Sleeveless cropped top"
            //   "weight" => 1.0
            //   "notes" => null
            //   "delivery_type" => "0"
            //   "remote_order_id" => "ORD-00167"
            //   "receiver" => "Test Client"
            //   "phone1" => "01000000000"
            //   "phone2" => ""
            //   "is_order" => "0"
            //   "is_fragile" => 0
            //   "return_amount" => 110.0
            //   "remote_shipment_id" => null
            //   "return_summary" => null
            //   "invoice_number" => "ORD-00167"
            //   "can_open" => 1
            //   "number_of_items" => 1
            // ]
            // array:24 [▼ // app/Services/Turbo/TurboApiService.php:113
            //   "main_client_code" => "25847"
            //   "authentication_key" => "39884f75bccb609497d058bbca569e14e0ca446cb16f69d96cff663449456819"
            //   "second_client" => "Tagmedix"
            //   "government" => "الإسكندرية"
            //   "area" => "محمور المحمودية من اول الريادة الى شارع الشرطة"
            //   "address" => "test address"
            //   "api_followup_phone" => "01000000000"
            //   "amount_to_be_collected" => 110.0
            //   "order_summary" => "1x Sleeveless cropped top"
            //   "weight" => 1.0
            //   "notes" => null
            //   "delivery_type" => "0"
            //   "remote_order_id" => "ORD-00167"
            //   "receiver" => "Test Client"
            //   "phone1" => "01000000000"
            //   "phone2" => ""
            //   "is_order" => "0"
            //   "is_fragile" => 0
            //   "return_amount" => 110.0
            //   "remote_shipment_id" => null
            //   "return_summary" => null
            //   "invoice_number" => "ORD-00167"
            //   "can_open" => 1
            //   "number_of_items" => 1
            // ]
            /*
        |--------------------------------------------------------------------------
        | Send shipment to Turbo
        |--------------------------------------------------------------------------
        */
            $response = $this->turboApiService->addOrder(
                $shipmentPayload
            );

            $executionTime = microtime(true) - $startedAt;

            /*
|--------------------------------------------------------------------------
| Extract Turbo Response
|--------------------------------------------------------------------------
*/

            $turboResult = data_get($response, 'result', []);

            $turboCode = data_get($turboResult, 'code') ?? data_get($response, 'code');

            $turboInvoiceNumber = data_get(
                $turboResult,
                'invoice_number'
            ) ?? data_get($response, 'invoice_number');

            $turboBarcode = data_get(
                $turboResult,
                'bar_code'
            ) ?? data_get($response, 'bar_code');

            if (empty($turboCode) && empty($turboBarcode)) {
                $errMsg = $response['error_msg'] ?? $response['message'] ?? 'فشل طلب الشحن من شركة Turbo ولم يتم استلام كود تتبع';
                throw new \Exception($errMsg);
            }

            $turboIsOrder = data_get(
                $turboResult,
                'is_order'
            );

            $expectedBranch = data_get(
                $turboResult,
                'expected_branch'
            );

            $overallDeliveryRate = data_get(
                $turboResult,
                'overall_delivery_rate'
            );

            $firstTimeSuccessRate = data_get(
                $turboResult,
                'first_time_success_rate'
            );

            /*
|--------------------------------------------------------------------------
| Remote Shipment ID
|--------------------------------------------------------------------------
|
| Turbo في الـ response الحالي لا يرجع id مستقل للشحنة.
| لذلك لا نفترض وجود remote_shipment_id.
|
*/

            $remoteShipmentId =
                $validated['remote_shipment_id']
                ?? null;

            /*
|--------------------------------------------------------------------------
| Tracking / Barcode
|--------------------------------------------------------------------------
*/

            $trackingNumber = $turboBarcode;

            /*
        |--------------------------------------------------------------------------
        | Save old status
        |--------------------------------------------------------------------------
        */

            $oldOrderStatus = $order->status;

            /*
        |--------------------------------------------------------------------------
        | Update order
        |--------------------------------------------------------------------------
        */

            $order->update([
                'status' => 'shipped',
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Shipment
        |--------------------------------------------------------------------------
        */

            $shipment = Shipment::create([

                /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

                'order_id' => $order->id,

                /*
    |--------------------------------------------------------------------------
    | Turbo Identifiers
    |--------------------------------------------------------------------------
    */

                // رقم الشحنة / الكود الذي أعادته Turbo
                'turbo_id' => $turboCode,

                // رقم الأوردر عندنا
                'remote_order_id' => $shipmentPayload['remote_order_id'],

                // Turbo لا يرجع remote shipment id حاليًا
                'remote_shipment_id' => $remoteShipmentId,

                // Turbo barcode
                'tracking_number' => $trackingNumber,

                /*
    |--------------------------------------------------------------------------
    | Customer Information
    |--------------------------------------------------------------------------
    */

                'receiver' => $shipmentPayload['receiver'],

                'phone' => $shipmentPayload['phone1'],

                'phone2' => $shipmentPayload['phone2'],

                'api_followup_phone' =>
                $shipmentPayload['api_followup_phone'],

                /*
    |--------------------------------------------------------------------------
    | Address
    |--------------------------------------------------------------------------
    */

                'government' => $government->name,

                'area' => $area?->name,

                'address' =>
                $shipmentPayload['address'],

                /*
    |--------------------------------------------------------------------------
    | Order Financial Data
    |--------------------------------------------------------------------------
    */

                'amount' =>
                $shipmentPayload['amount_to_be_collected'],

                'collected_amount' => 0,

                /*
    |--------------------------------------------------------------------------
    | Order Information
    |--------------------------------------------------------------------------
    */

                'weight' =>
                $shipmentPayload['weight'],

                'notes' =>
                $shipmentPayload['notes'],

                'order_summary' =>
                $shipmentPayload['order_summary'],

                'return_summary' =>
                $shipmentPayload['return_summary'],

                'return_amount' =>
                $shipmentPayload['return_amount'],

                /*
    |--------------------------------------------------------------------------
    | Shipment Options
    |--------------------------------------------------------------------------
    */

                'delivery_type' =>
                (string) $shipmentPayload['delivery_type'],

                'is_order' =>
                (bool) $shipmentPayload['is_order'],

                'is_fragile' =>
                (bool) $shipmentPayload['is_fragile'],

                'can_open' =>
                (bool) $shipmentPayload['can_open'],

                'number_of_items' =>
                $shipmentPayload['number_of_items'],

                /*
    |--------------------------------------------------------------------------
    | Invoice
    |--------------------------------------------------------------------------
    |
    | نأخذ رقم الفاتورة الذي رجع من Turbo لو موجود،
    | وإلا نستخدم رقم الأوردر عندنا.
    |
    */

                'invoice_number' =>
                $turboInvoiceNumber
                    ?? $shipmentPayload['invoice_number'],

                /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

                'status' => 'shipped',

                'status_text' =>
                'تم إرسال الطلب لشركة Turbo بنجاح',

                'internal_status' => 'shipped',

                /*
    |--------------------------------------------------------------------------
    | Dates
    |--------------------------------------------------------------------------
    */

                'shipped_at' => now(),

                'last_synced_at' => now(),

                /*
    |--------------------------------------------------------------------------
    | Full Request Snapshot
    |--------------------------------------------------------------------------
    */

                'payload' => $shipmentPayload,

                /*
    |--------------------------------------------------------------------------
    | Full Turbo Response Snapshot
    |--------------------------------------------------------------------------
    */

                'response' => $response,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Shipment Status History
        |--------------------------------------------------------------------------
        */

            $shipment->statusHistories()->create([
                'old_status' => null,

                'new_status' => 'shipped',

                'response' => [
                    'message' => 'Shipment created successfully',
                    'turbo_id' => $turboCode,
                    'tracking_number' => $trackingNumber,
                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | API Log
        |--------------------------------------------------------------------------
        */

            $shipment->logs()->create([
                'endpoint' => 'addOrder',

                'request' => $shipmentPayload,

                'response' => $response,

                'status_code' => 200,

                'execution_time' => $executionTime,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Log Order Status Change
        |--------------------------------------------------------------------------
        */

            Log::info('Order shipped successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldOrderStatus,
                'new_status' => 'shipped',
                'shipment_id' => $shipment->id,
                'turbo_id' => $turboCode,
                'tracking_number' => $trackingNumber,
                'execution_time' => $executionTime,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

            if ($request->wantsJson() || $request->ajax()) {

                return response()->json([
                    'success' => true,

                    'message' =>
                    'تم شحن الطلب بنجاح عبر شركة Turbo وتمت إضافته إلى الشحنات',

                    'order_id' => $order->id,
                    'order_number' => $order->order_number,

                    'shipment_id' => $shipment->id,

                    'turbo_id' => $turboCode,

                    'tracking_number' => $trackingNumber,

                    'receiver' => $shipmentPayload['receiver'],
                    'phone' => $shipmentPayload['phone1'],
                    'government' => $government->name,
                    'address' => $shipmentPayload['address'],
                    'amount_to_be_collected' => $shipmentPayload['amount_to_be_collected'],
                    'return_amount' => $shipmentPayload['return_amount'],
                    'shipment_url' => route('shipments.index', ['q' => $turboCode ?: $order->order_number]),
                    'order_url' => route('orders.show', $order->id),

                    'data' => $response,
                ]);
            }

            return redirect()
                ->route('finished-goods.index')
                ->with(
                    'success',
                    'تم شحن الطلب (' .
                        $order->order_number .
                        ') بنجاح عبر شركة Turbo!'
                );
        } catch (Exception $e) {

            $executionTime = microtime(true) - $startedAt;

            /*
        |--------------------------------------------------------------------------
        | If shipment was created, log exception there
        |--------------------------------------------------------------------------
        */

            Log::error(
                'Shipment Error for order ' . $order->id,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payload' => $shipmentPayload,
                    'execution_time' => $executionTime,
                    'exception' => $e->getMessage(),
                ]
            );

            /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

            if (
                $request->wantsJson()
                || $request->ajax()
            ) {

                return response()->json([
                    'success' => false,

                    'message' =>
                    'فشل شحن الطلب: ' .
                        $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'فشل شحن الطلب: ' .
                        $e->getMessage()
                );
        }
    }
}
