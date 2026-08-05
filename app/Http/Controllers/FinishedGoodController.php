<?php

namespace App\Http\Controllers;

use App\Integrations\Turbo\Models\Shipment;
use App\Models\Order;
use App\Services\Turbo\TurboApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'total_remaining' => $orders->sum(fn ($order) => $order->getRemainingAmount()),
        ];

        return view('pages.finished-goods.index', compact('orders', 'stats', 'search'));
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
        $order = Order::with(['customer', 'address', 'items'])->findOrFail($id);

        $customerName = $order->customer?->name ?? $order->customer_name ?? '';
        $phone1 = $order->customer?->phone_1 ?? $order->phone_1 ?? '';
        $phone2 = $order->customer?->phone_2 ?? $order->phone_2 ?? '';
        $governorate = $order->address?->governorate ?? $order->governorate ?? '';
        $fullAddress = $order->address?->full_address ?? $order->full_address ?? '';
        $defaultGovernment = $order->address?->turbo_government
            ?? ($governorate ? 'مصر-' . $governorate : '');
        $defaultArea = $order->address?->turbo_area ?? '';

        $validated = $request->validate([
            'government'             => 'required|string',
            'area'                   => 'nullable|string',
            'address'                => 'required|string',
            'api_followup_phone'     => 'nullable|string',
            'amount_to_be_collected' => 'nullable|numeric',
            'order_summary'          => 'nullable|string',
            'weight'                 => 'nullable|numeric',
            'notes'                  => 'nullable|string',
            'delivery_type'          => 'nullable',
            'remote_order_id'        => 'nullable|string',
            'receiver'               => 'required|string',
            'phone1'                 => 'required|string',
            'phone2'                 => 'nullable|string',
            'is_order'               => 'nullable',
            'is_fragile'             => 'nullable',
            'return_amount'          => 'nullable|numeric',
            'remote_shipment_id'     => 'nullable|string',
            'return_summary'         => 'nullable|string',
            'invoice_number'         => 'nullable|string',
        ]);

        try {
            $shipmentPayload = [
                'government'             => $validated['government'] ?? $defaultGovernment,
                'area'                   => $validated['area'] ?? $defaultArea ?: null,
                'address'                => $validated['address'] ?? $fullAddress,
                'api_followup_phone'     => $validated['api_followup_phone'] ?? $phone1,
                'amount_to_be_collected' => isset($validated['amount_to_be_collected']) ? (float) $validated['amount_to_be_collected'] : $order->getRemainingAmount(),
                'order_summary'          => $validated['order_summary'] ?? null,
                'weight'                 => isset($validated['weight']) ? (float) $validated['weight'] : 1,
                'notes'                  => $validated['notes'] ?? null,
                'delivery_type'          => $validated['delivery_type'] ?? 0,
                'remote_order_id'        => $validated['remote_order_id'] ?? $order->order_number,
                'receiver'               => $validated['receiver'] ?? $customerName,
                'phone1'                 => $validated['phone1'] ?? $phone1,
                'phone2'                 => $validated['phone2'] ?? $phone2,
                'is_order'               => (string) ($validated['is_order'] ?? '0'),
                'is_fragile'             => (int) ($validated['is_fragile'] ?? 0),
                'return_amount'          => isset($validated['return_amount']) ? (float) $validated['return_amount'] : 0,
                'remote_shipment_id'     => $validated['remote_shipment_id'] ?? null,
                'return_summary'         => $validated['return_summary'] ?? null,
                'invoice_number'         => $validated['invoice_number'] ?? $order->order_number,
            ];

            // Send POST request to Turbo API
            $response = $this->turboApiService->addOrder($shipmentPayload);

            // Extract Turbo ID safely from multi-layer responses
            $turboCode = $response['data'][0]['code'] 
                ?? $response['data']['code'] 
                ?? $response['code'] 
                ?? null;

            // Update local order status
            $order->update(['status' => 'shipped']);

            // Save record to local shipments table
            if (class_exists(Shipment::class)) {
                try {
                    Shipment::create([
                        'turbo_id'        => $turboCode,
                        'remote_order_id' => $order->order_number,
                        'receiver'        => $shipmentPayload['receiver'],
                        'phone'           => $shipmentPayload['phone1'],
                        'phone2'          => $shipmentPayload['phone2'],
                        'government'      => $shipmentPayload['government'],
                        'area'            => $shipmentPayload['area'],
                        'address'         => $shipmentPayload['address'],
                        'amount'          => $shipmentPayload['amount_to_be_collected'],
                        'weight'          => $shipmentPayload['weight'],
                        'notes'           => $shipmentPayload['notes'],
                        'order_summary'   => $shipmentPayload['order_summary'],
                        'status'          => 'shipped',
                        'status_text'     => 'تم إرسال الطلب لشركة Turbo بنجاح',
                        'delivery_type'   => (string) $shipmentPayload['delivery_type'],
                        'is_order'        => (bool) $shipmentPayload['is_order'],
                        'is_fragile'      => (bool) $shipmentPayload['is_fragile'],
                        'payload'         => $shipmentPayload,
                        'response'        => $response,
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to save Shipment record locally: ' . $e->getMessage());
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم شحن الطلب بنجاح عبر شركة Turbo',
                    'data'    => $response,
                ]);
            }

            return redirect()->route('finished-goods.index')
                ->with('success', 'تم شحن الطلب (' . $order->order_number . ') بنجاح عبر شركة Turbo!');

        } catch (Exception $e) {
            Log::error('Shipment Error for order ' . $order->id . ': ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل شحن الطلب: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل شحن الطلب: ' . $e->getMessage());
        }
    }
}