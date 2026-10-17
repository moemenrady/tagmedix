<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Modules\Inventory\Models\InventoryFinishedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnItemController extends Controller
{
    /**
     * Display a listing of returns.
     */
    public function index(Request $request)
    {
        $query = OrderReturn::with(['order.customer', 'customer', 'items.orderItem'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('return_number', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($oq) use ($search) {
                            $oq->where('order_number', 'like', "%{$search}%")
                                ->orWhere('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_phone_1', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, fn($q, $st) => $q->where('status', $st))
            ->when($request->return_type, fn($q, $rt) => $q->where('return_type', $rt))
            ->when($request->qc_status, fn($q, $qc) => $q->where('qc_status', $qc));

        $stats = [
            'total_returns_count' => (clone $query)->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'inspected_count' => (clone $query)->where('status', 'inspected')->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
            'total_refund_amount' => (clone $query)->sum('refund_amount'),
        ];

        $returns = $query->latest()->paginate(15)->withQueryString();

        return view('pages.returns.index', compact('returns', 'stats'));
    }

    /**
     * Show the form for creating a new return request.
     */
    public function create(Request $request)
    {
        $selectedOrder = null;
        if ($request->has('order_id')) {
            $selectedOrder = Order::with(['items.style', 'items.color', 'items.size', 'items.type', 'customer'])
                ->find($request->order_id);
        } elseif ($request->has('search')) {
            $selectedOrder = Order::with(['items.style', 'items.color', 'items.size', 'items.type', 'customer'])
                ->where('order_number', 'like', "%{$request->search}%")
                ->orWhere('customer_phone_1', 'like', "%{$request->search}%")
                ->first();
        }

        $recentOrders = Order::latest()->take(25)->get(['id', 'order_number', 'customer_name', 'customer_phone_1', 'total_price', 'created_at']);

        return view('pages.returns.create', compact('selectedOrder', 'recentOrders'));
    }

    /**
     * Store a newly created return request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'return_type' => 'required|in:full,partial',
            'reason' => 'required|string|max:255',
            'shipping_cost_deducted' => 'nullable|numeric|min:0',
            'action_taken' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $order = Order::with('items')->findOrFail($validated['order_id']);

            $returnNumber = OrderReturn::generateReturnNumber();
            $shippingDeducted = (float)($validated['shipping_cost_deducted'] ?? 0);

            $orderReturn = OrderReturn::create([
                'return_number' => $returnNumber,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_type' => $validated['return_type'],
                'reason' => $validated['reason'],
                'status' => 'pending',
                'qc_status' => 'pending',
                'shipping_cost_deducted' => $shippingDeducted,
                'refund_status' => 'pending',
                'action_taken' => $validated['action_taken'] ?? 'استرجاع للمخزن',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalItemsRefund = 0;

            foreach ($validated['items'] as $itemData) {
                $orderItem = OrderItem::find($itemData['order_item_id']);
                $qty = min($itemData['quantity'], $orderItem->quantity);
                $unitPrice = (float) $orderItem->price;
                $lineTotal = $qty * $unitPrice;
                $totalItemsRefund += $lineTotal;

                ReturnItem::create([
                    'order_return_id' => $orderReturn->id,
                    'order_item_id' => $orderItem->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'qc_result' => 'uninspected',
                    'is_restocked' => false,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $netRefund = max(0, $totalItemsRefund - $shippingDeducted);
            $orderReturn->update(['refund_amount' => $netRefund]);

            return redirect()->route('returns.show', $orderReturn->id)
                ->with('success', "تم تسجيل طلب المرتجع بنجاح برقم: {$returnNumber}");
        });
    }

    /**
     * Display the specified return.
     */
    public function show($id)
    {
        $orderReturn = OrderReturn::with([
            'order.items',
            'order.customer',
            'customer',
            'creator',
            'items.orderItem.style',
            'items.orderItem.color',
            'items.orderItem.size',
            'items.orderItem.type',
        ])->findOrFail($id);

        return view('pages.returns.show', compact('orderReturn'));
    }

    /**
     * Process Quality Check (QC) and optionally restock good items into Finished Goods.
     */
    public function processQc(Request $request, $id)
    {
        $orderReturn = OrderReturn::with('items.orderItem')->findOrFail($id);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:return_items,id',
            'items.*.qc_result' => 'required|in:good,damaged',
            'restock' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($orderReturn, $validated, $request) {
            $hasGood = false;
            $hasDamaged = false;
            $shouldRestock = $request->boolean('restock', true);

            foreach ($validated['items'] as $itemInput) {
                $returnItem = ReturnItem::find($itemInput['id']);
                $qcResult = $itemInput['qc_result'];

                if ($qcResult === 'good') {
                    $hasGood = true;
                    // Restock into Finished Goods if requested and not already restocked
                    if ($shouldRestock && !$returnItem->is_restocked) {
                        $this->restockToFinishedGoods($returnItem->orderItem, $returnItem->quantity);
                        $returnItem->is_restocked = true;
                    }
                } else {
                    $hasDamaged = true;
                }

                $returnItem->qc_result = $qcResult;
                $returnItem->save();
            }

            $overallQc = 'pending';
            if ($hasGood && $hasDamaged) {
                $overallQc = 'partial_good';
            } elseif ($hasGood) {
                $overallQc = 'good';
            } elseif ($hasDamaged) {
                $overallQc = 'damaged';
            }

            $orderReturn->update([
                'qc_status' => $overallQc,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return back()->with('success', 'تم اعتماد نتيجة الفحص وإعادة الأصناف السليمة لمخزون المنتجات التامة بنجاح.');
    }

    /**
     * Mark refund as completed.
     */
    public function processRefund(Request $request, $id)
    {
        $orderReturn = OrderReturn::findOrFail($id);

        $orderReturn->update([
            'refund_status' => 'refunded',
            'action_taken' => $request->input('action_taken', $orderReturn->action_taken),
        ]);

        return back()->with('success', 'تم تسجيل استرداد المبلغ للعميل بنجاح.');
    }

    /**
     * Restock an order item to Finished Goods.
     */
    private function restockToFinishedGoods(OrderItem $orderItem, int $quantity): void
    {
        $finishedProduct = InventoryFinishedProduct::firstOrNew([
            'style_id' => $orderItem->style_id,
            'color_id' => $orderItem->color_id,
            'size_id' => $orderItem->size_id,
            'type_id' => $orderItem->type_id,
            'pant_type_id' => $orderItem->pant_type_id,
            'sleeve_type_id' => $orderItem->sleeve_type_id,
        ]);

        $prevQty = $finishedProduct->exists ? (float) $finishedProduct->quantity : 0;
        $finishedProduct->quantity = $prevQty + $quantity;
        $finishedProduct->is_active = true;
        if (!$finishedProduct->exists) {
            $finishedProduct->unit_cost = (float) $orderItem->price;
            $finishedProduct->total_cost = $finishedProduct->quantity * $finishedProduct->unit_cost;
            $finishedProduct->product_code = 'RESTOCK-' . ($orderItem->style_id ?? '0') . '-' . ($orderItem->color_id ?? '0');
        } else {
            $finishedProduct->total_cost = $finishedProduct->quantity * $finishedProduct->unit_cost;
        }

        $finishedProduct->save();
    }

    /**
     * Remove the specified return.
     */
    public function destroy($id)
    {
        $orderReturn = OrderReturn::with('items')->findOrFail($id);

        if ($orderReturn->items->where('is_restocked', true)->count() > 0) {
            return back()->with('error', 'لا يمكن حذف طلب مرتجع تم إعادة أصنافه للمخزن بالفعل.');
        }

        $orderReturn->delete();

        return redirect()->route('returns.index')->with('success', 'تم حذف طلب المرتجع بنجاح.');
    }
}
