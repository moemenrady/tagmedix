<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Turbo\TurboApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Integrations\Turbo\Models\Shipment;

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

        $shipmentPayload = [
            'second_client' => 'Tagmedix',

            'government' => $validated['government']
                ?? $governorate,

            'area' => $validated['area']
                ?? null,

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

            'remote_shipment_id' => $validated['remote_shipment_id'] ?? null,

            'return_summary' => $validated['return_summary'] ?? null,

            'invoice_number' => $validated['invoice_number'] ?? $order->order_number,

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

            /*
|--------------------------------------------------------------------------
| Turbo Data
|--------------------------------------------------------------------------
*/

            $turboCode = data_get($turboResult, 'code');

            $turboInvoiceNumber = data_get(
                $turboResult,
                'invoice_number'
            );

            $turboBarcode = data_get(
                $turboResult,
                'bar_code'
            );

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

            // $order->update([
            //     'status' => 'shipped',
            // ]);

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

                'government' =>
                $shipmentPayload['government'],

                'area' =>
                $shipmentPayload['area'],

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
                    'تم شحن الطلب بنجاح عبر شركة Turbo',

                    'shipment_id' => $shipment->id,

                    'turbo_id' => $turboCode,

                    'tracking_number' => $trackingNumber,

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
