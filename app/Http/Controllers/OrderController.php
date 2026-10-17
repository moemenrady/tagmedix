<?php

namespace App\Http\Controllers;

use App\Integrations\Turbo\Services\TurboService;
use App\Models\Color;
use App\Models\Government;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function show($id)
    {
        $order = Order::with([
            'customer',
            'address',

            'items.type',
            'items.style',
            'items.color',
            'items.size',
            'items.pantType',
            'items.sleeveType',

            'payments.creator',
            'shipments',
        ])
            ->withSum('payments', 'amount')
            ->findOrFail($id);

        $colors = Color::all();
        $sizes = Size::all();
        $styles = Style::all();
        $types = Type::all();
        $pantTypes = PantType::all();
        $sleeveTypes = SleeveType::all();

        return view(
            'pages.orders.show',
            compact(
                'order',
                'colors',
                'sizes',
                'styles',
                'types',
                'pantTypes',
                'sleeveTypes'
            )
        );
    }

    public function index(Request $request)
    {
        $search = $request->query('q');
        $status = $request->query('status', 'all');
        $urgency = $request->query('urgency', 'all');
        $sort = $request->query('sort', 'latest');

        // 1. استعلام ذكي وسريع مع الترقيم (Pagination) بدلاً من get()
        $orders = Order::query()
            ->with(['customer', 'address', 'shipments'])
            ->withSum('payments', 'amount')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhere('customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_phone_1', 'LIKE', "%{$search}%")
                        ->orWhere('customer_phone_2', 'LIKE', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('phone_1', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($urgency === 'urgent', function ($query) {
                $query->where('is_urgent', true);
            })
            ->when($urgency === 'normal', function ($query) {
                $query->where('is_urgent', false);
            })
            ->when($sort === 'price_desc', function ($query) {
                $query->orderByDesc('total_price');
            })
            ->when($sort === 'price_asc', function ($query) {
                $query->orderBy('total_price');
            })
            ->when($sort === 'urgent_first', function ($query) {
                $query->orderByDesc('is_urgent')->latest('id');
            })
            ->when($sort === 'delivery_soonest', function ($query) {
                $query->orderByRaw('delivery_date IS NULL, delivery_date ASC');
            })
            ->when(!in_array($sort, ['price_desc', 'price_asc', 'urgent_first', 'delivery_soonest']), function ($query) {
                $query->latest('id'); // الترتيب الافتراضي حسب تاريخ الإضافة بدون أولويات
            })
            ->paginate(15) // ترقيم لعرض 15 طلب فقط للحفاظ على خفة المتصفح
            ->withQueryString();

        // 2. حساب الإحصائيات مباشرة من قاعدة البيانات باستعلام واحد فقط
        $statsQuery = Order::selectRaw('
    COUNT(id) as total_orders,
    SUM(CASE WHEN is_urgent = 1 THEN 1 ELSE 0 END) as urgent_orders,
    SUM(CASE WHEN status IN ("new", "confirmed", "cutting", "sewing", "finishing", "ready-to-ship") THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN source != "replacement" THEN total_price ELSE 0 END) as total_revenue
')->first();

        // حساب إجمالي المدفوعات من جدول الدفع مباشرة
        $paidAmount = \Illuminate\Support\Facades\DB::table('payments')->sum('amount') ?? 0;

        // حساب إجمالي الإيرادات
        $totalRevenue = $statsQuery->total_revenue ?? 0;

        $stats = [
            'total_orders' => $statsQuery->total_orders ?? 0,
            'urgent_orders' => $statsQuery->urgent_orders ?? 0,
            'processing_orders' => $statsQuery->processing_orders ?? 0,
            'total_revenue' => $totalRevenue,
            'paid_amount' => $paidAmount,

            // حساب المتبقي برمجياً: (إجمالي الإيرادات - المدفوع)
            // نستخدم max(0, ...) لمنع ظهور قيم بالسالب إذا كان المدفوع أكبر من الإيرادات
            'remaining_payments' => max(0, $totalRevenue - $paidAmount),
        ];
        $statusMap = [
            'new' => ['label' => 'طلب جديد', 'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
            'confirmed' => ['label' => 'تم التأكيد', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'cutting' => ['label' => 'مرحلة القص', 'class' => 'bg-orange-50 text-orange-700 border-orange-200'],
            'sewing' => ['label' => 'مرحلة الخياطة', 'class' => 'bg-violet-50 text-violet-700 border-violet-200'],
            'finishing' => ['label' => 'التشطيب', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'ready-to-ship' => ['label' => 'جاهز للشحن', 'class' => 'bg-cyan-50 text-cyan-700 border-cyan-200'],
            'shipped' => ['label' => 'تم الشحن', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'delivered' => ['label' => 'تم التسليم', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'canceled' => ['label' => 'ملغي', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
        ];

        return view('pages.orders.index', compact('orders', 'stats', 'statusMap', 'search', 'urgency', 'sort'));
    }

    // استدعاء TurboService داخل OrderController
    // app/Http/Controllers/OrderController.php

    // use App\Integrations\Turbo\Services\TurboService;
    // 1. دالة عرض صفحة الإنشاء
    public function create(TurboService $turboService)
    {
        $colors = Color::all();
        $sizes = Size::all();
        $styles = Style::all();
        $types = Type::all();
        $pantTypes = PantType::all();
        $sleeveType = SleeveType::all();

        $finishedProducts = \Illuminate\Support\Facades\DB::table('inventory_finished_products')
            ->join('inventory_items', 'inventory_finished_products.inventory_item_id', '=', 'inventory_items.id')
            ->where('inventory_finished_products.is_active', true)
            ->select(
                'inventory_finished_products.id',
                'inventory_finished_products.product_code',
                'inventory_items.name as product_name',
                'inventory_finished_products.pant_type_id',
                'inventory_finished_products.type_id',
                'inventory_finished_products.style_id',
                'inventory_finished_products.color_id',
                'inventory_finished_products.size_id',
                'inventory_finished_products.sleeve_type_id',
                'inventory_finished_products.quantity as available_qty'
            )->get();

        $governments = Government::select('id', 'name')->get();

        $orderItemStanders = \App\Models\OrderItemStander::with(['pantType', 'type', 'style', 'color', 'size', 'sleeveType'])
            ->where('is_active', true)
            ->get();

        $defaultDeliveryDays = (int) \App\Models\Setting::get('default_delivery_days', 7);
        $urgentDeliveryDays = (int) \App\Models\Setting::get('urgent_delivery_days', 3);

        return view('pages.orders.create', compact(
            'colors',
            'sizes',
            'styles',
            'types',
            'pantTypes',
            'governments',
            'sleeveType',
            'finishedProducts',
            'orderItemStanders',
            'defaultDeliveryDays',
            'urgentDeliveryDays'
        ));
    }
    public function getAreasByGovernment($governmentId) // تم إزالة حقن TurboService
    {
        try {
            // سحب المناطق المرتبطة بالمحافظة من داتابيز سيرفرك
            $areas = \App\Models\Area::where('government_id', $governmentId)
                ->select('id', 'name')
                ->get();

            return response()->json($areas);
        } catch (\Throwable $e) {
            \Log::error('Local Areas Fetch Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch areas'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $order = $this->orderService->store($request);

            return redirect()
                ->route('orders.index')
                ->with('success', 'تم إنشاء الطلب بنجاح');
        } catch (ValidationException $e) {
            throw $e; // يعيد المستخدم للفورم مع إبقاء البيانات القديمة كاملة
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('fail', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $order = Order::with([
            'items',
            'payments',
        ])->withSum('payments', 'amount')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Edit Order Page',
            'data' => $order,
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::with(['items', 'payments', 'customer', 'address'])->findOrFail($id);

        // --- 1. التعامل مع طلبات التحديث الجزئية (AJAX / JSON) من صفحة الـ Show ---
        if ($request->wantsJson() || $request->ajax()) {
            return $this->handleAjaxUpdate($request, $order);
        }

        // --- 2. التعامل مع الرفع التقليدي (Full Form Submit) ---
        // (الكود التقليدي الخاص بك يبقى كما هو هنا لحفظ النموذج العادي)
        // ...
    }

    /**
     * معالجة طلبات الـ AJAX القادمة من Alpine.js في صفحة عرض الطلب
     */
    private function handleAjaxUpdate(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $message = 'تم التحديث بنجاح';

            if ($request->has('status')) {
                $order->update(['status' => $request->status]);
                $message = 'تم تحديث حالة الطلب بنجاح';
            } elseif ($request->has('customer_info')) {
                $this->updateCustomerData($order, $request->customer_info);
                $message = 'تم تحديث بيانات العميل بنجاح';
            } elseif ($request->has('update_financials')) {
                $this->updateOrderFinancials($order, $request->paid_amount);
                $message = 'تم تحديث المدفوعات بنجاح';
            } elseif ($request->has('sync_items')) {
                $this->syncOrderItems($order, $request->sync_items);
                $message = 'تم تحديث منتجات الطلب بنجاح';
            } else {
                throw new \Exception('لم يتم إرسال بيانات صالحة للتحديث.');
            }

            DB::commit();

            // إرجاع الطلب محدثاً بالبيانات ليعكسها Alpine.js في الواجهة
            return response()->json([
                'success' => true,
                'message' => $message,
                'order' => $order->fresh(['items.type', 'items.style', 'items.color', 'items.size', 'customer', 'address']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث بيانات العميل والعنوان
     */
    private function updateCustomerData(Order $order, array $info)
    {
        $phone1 = isset($info['phone1']) ? $this->orderService->cleanPhoneNumber($info['phone1']) : ($order->customer?->phone_1 ?? $order->customer_phone_1);
        $phone2 = isset($info['phone2']) ? $this->orderService->cleanPhoneNumber($info['phone2']) : ($order->customer?->phone_2 ?? $order->customer_phone_2);
        $name = $info['name'] ?? ($order->customer?->name ?? $order->customer_name);
        $insta = $info['insta'] ?? ($order->customer?->instagram_profile ?? $order->customer_instagram);

        if ($order->customer) {
            $order->customer->update([
                'name'              => $name,
                'phone_1'           => $phone1,
                'phone_2'           => $phone2,
                'instagram_profile' => $insta,
            ]);
        }

        $order->update([
            'customer_name'      => $name,
            'customer_phone_1'   => $phone1,
            'customer_phone_2'   => $phone2,
            'customer_instagram' => $insta,
        ]);

        if ($order->address) {
            $order->address->update([
                'governorate'  => $info['gov'] ?? $order->address->governorate,
                'full_address' => $info['address'] ?? $order->address->full_address,
            ]);
        }
    }

    /**
     * تحديث الأمور المالية (المدفوع والمتبقي)
     */
    private function updateOrderFinancials(Order $order, $newPaidAmount)
    {
        // حذف الدفعات القديمة المرتبطة وتسجيل الدفعة الجديدة
        $order->payments()->delete();

        if ($newPaidAmount > 0) {
            $order->payments()->create([
                'type'         => 'order_advance',
                'amount'       => $newPaidAmount,
                'payment_date' => now(),
                'note'         => 'تعديل من واجهة تفاصيل الطلب',
                'created_by'   => auth()->id(),
            ]);
        }

        // إعادة حساب المتبقي
        $order->update([
            'remaining_amount' => $order->total_price - $newPaidAmount,
        ]);
    }

    /**
     * مزامنة المنتجات (حذف القديم وإضافة الجديد مع حساب الإجمالي)
     */
    private function syncOrderItems(Order $order, array $items)
    {
        $order->items()->delete();
        $totalPrice = 0;

        foreach ($items as $item) {
            $qty = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $itemTotal = $qty * $price;

            $order->items()->create([
                'type_id'        => $item['type_id'] ?? null,
                'pant_type_id'   => $item['pant_type_id'] ?? null,
                'style_id'       => $item['style_id'] ?? null,
                'color_id'       => $item['color_id'] ?? null,
                'size_id'        => $item['size_id'] ?? null,
                'sleeve_type_id' => $item['sleeve_type_id'] ?? null,
                'quantity'       => $qty,
                'price'          => $price,
                'total'          => $itemTotal,
                'notes'          => $item['notes'] ?? null,
            ]);

            $totalPrice += $itemTotal;
        }

        // تحديث إجمالي الفاتورة والمتبقي
        $totalPaid = $order->payments()->sum('amount');
        $order->update([
            'total_price'      => $totalPrice,
            'remaining_amount' => $totalPrice - $totalPaid,
        ]);
    }
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف الطلب وجميع البيانات المرتبطة به بنجاح.');
    }

    public function restore($id)
    {
        $order = Order::findOrFail($id);

        $order->restore();

        return response()->json([
            'message' => 'Order restored successfully',
        ]);
    }

    public function forceDelete($id)
    {
        $order = Order::findOrFail($id);

        $order->forceDelete();

        return response()->json([
            'message' => 'Order permanently deleted',
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,confirmed,cutting,sewing,finishing,ready-to-ship,shipped,delivered,canceled',
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'تم تحديث الحالة بنجاح',
            'data' => $order,
        ]);
    }

    public function invoice($id)
    {
        $order = Order::with([
            'items',
            'payments',
        ])->withSum('payments', 'amount')
            ->findOrFail($id);

        return response()->json([
            'invoice' => $order,
        ]);
    }

    public function printInvoice($id)
    {
        $order = Order::with('items', 'payments')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Print invoice',
            'data' => $order,
        ]);
    }

    public function addItem(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([

            'design' => 'required|string',

            'color' => 'required|string',

            'size' => 'required|string',

            'quantity' => 'required|integer|min:1',

            'price' => 'required|numeric|min:0',
        ]);

        $itemTotal =
            $request->price * $request->quantity;

        $order->items()->create([

            'product_id' => $request->product_id,

            'design' => $request->design,

            'color' => $request->color,

            'size' => $request->size,

            'sleeve' => $request->sleeve,

            'quantity' => $request->quantity,

            'price' => $request->price,

            'total' => $itemTotal,

            'notes' => $request->notes,
        ]);

        $this->recalculateOrder($order);

        return response()->json([
            'message' => 'Item added successfully',
            'data' => $order->load('items'),
        ]);
    }

    public function removeItem($id)
    {
        $item = OrderItem::findOrFail($id);

        $order = $item->order;

        $item->delete();

        $this->recalculateOrder($order);

        return response()->json([
            'message' => 'Item removed successfully',
            'data' => $order->load('items'),
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($id);

        $remaining =
            $order->total_price - $request->paid_amount;

        $order->update([
            'paid_amount' => $request->paid_amount,
            'remaining_amount' => $remaining,
        ]);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $order,
        ]);
    }

    private function recalculateOrder(Order $order)
    {
        $total =
            $order->items()->sum('total');

        $order->update([

            'total_price' => $total,

            'remaining_amount' => $total - $order->paid_amount,
        ]);
    }
}
