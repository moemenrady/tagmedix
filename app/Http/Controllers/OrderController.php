<?php

namespace App\Http\Controllers;

use App\Integrations\Turbo\Services\TurboService;
use App\Models\Color;
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
        $search = $request->search;

        $orders = Order::query()
            ->with(['customer', 'address', 'items'])
            ->withSum('payments', 'amount')
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

            'processing_orders' => $orders->whereIn('status', [
                'confirmed',
                'cutting',
                'sewing',
                'finishing',
                'ready-to-ship',
            ])->count(),

            'total_revenue' => $orders
                ->where('source', '!=', 'replacement')
                ->sum('total_price'),

            'paid_amount' => $orders->sum(
                fn($order) => $order->getTotalPaid()
            ),

            'remaining_payments' => $orders->sum(
                fn($order) => $order->getRemainingAmount()
            ),
        ];
        $statusMap = [
            'new' => [
                'label' => 'طلب جديد',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],

            'confirmed' => [
                'label' => 'تم التأكيد',
                'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],

            'cutting' => [
                'label' => 'مرحلة القص',
                'class' => 'bg-orange-50 text-orange-700 border-orange-200',
            ],

            'sewing' => [
                'label' => 'مرحلة الخياطة',
                'class' => 'bg-violet-50 text-violet-700 border-violet-200',
            ],

            'finishing' => [
                'label' => 'التشطيب',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],

            'ready-to-ship' => [
                'label' => 'جاهز للشحن',
                'class' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            ],

            'shipped' => [
                'label' => 'تم الشحن',
                'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],

            'delivered' => [
                'label' => 'تم التسليم',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],

            'canceled' => [
                'label' => 'ملغي',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
        ];

        $sourceMap = [
            'new' => [
                'label' => 'طلب جديد',
                'icon' => 'fa-solid fa-plus',
                'class' => 'text-green-600',
            ],
            'from_stock' => [
                'label' => 'من المخزن',
                'icon' => 'fa-solid fa-box',
                'class' => 'text-blue-600',
            ],
            'replacement' => [
                'label' => 'استبدال',
                'icon' => 'fa-solid fa-rotate',
                'class' => 'text-orange-600',
            ],
        ];

        return view('pages.orders.index', compact(
            'orders',
            'stats',
            'statusMap',
            'sourceMap',
            'search'
        ));
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

        $governments = [];

        try {
            $response = $turboService->getGovernments();
            // استخراج البيانات من feed أو data أو المفتاح الرئيسي
            $governments = $response['feed'] ?? $response['data'] ?? (is_array($response) ? $response : []);
        } catch (\Throwable $e) {
            \Log::error('Turbo API Error: ' . $e->getMessage());
        }

        return view('pages.orders.create', compact(
            'colors',
            'sizes',
            'styles',
            'types',
            'pantTypes',
            'governments',
            'sleeveType'
        ));
    }

    public function getAreasByGovernment($governmentId, TurboService $turboService)
    {
        try {
            $response = $turboService->getAreas($governmentId);

            // استخراج المناطق من feed مباشرة
            $areas = $response['feed'] ?? $response['data'] ?? [];

            return response()->json($areas);
        } catch (\Throwable $e) {
            \Log::error('Turbo Areas API Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to fetch areas'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // 1. التحرُّك والتحقق من البيانات الأساسية الأول
            $validatedData = $request->validate([
                'customer_name' => 'required|string|max:255',
                'phone_1' => 'required|string|max:20',
                'address.governorate' => 'required|string',
                'address.area' => 'required|string',
                'address.full_address' => 'required|string|max:500',
                'items' => 'required|array|min:1',
            ]);

            $address = $request->input('address', []);
            $isInternational = ($address['governorate'] ?? '') === 'شحن دولي';

            // 2. تجهيز مصفوفة العنوان حسب نوع الشحن
            $addressPayload = [
                'country' => $isInternational ? 'دولى' : 'مصر',
                'governorate' => $address['governorate'] ?? '',
                'city' => $isInternational ? 'شحن دولي' : ($address['area'] ?? ''),
                'full_address' => $address['full_address'] ?? '',
            ];

            // دمج البيانات المعدلة في الـ Request
            $request->merge(['address' => $addressPayload]);

            // 3. الحفظ في الداتابيز
            $order = $this->orderService->store($request);

            return redirect()
                ->route('orders.index')
                ->with('success', 'تم إنشاء الطلب بنجاح');
        } catch (ValidationException $e) {
            throw $e; // بيرجعك للفورم مع الأخطاء
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('fail', 'حدث خطأ: ' . $e->getMessage());
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
            } 
            elseif ($request->has('customer_info')) {
                $this->updateCustomerData($order, $request->customer_info);
                $message = 'تم تحديث بيانات العميل بنجاح';
            } 
            elseif ($request->has('update_financials')) {
                $this->updateOrderFinancials($order, $request->paid_amount);
                $message = 'تم تحديث المدفوعات بنجاح';
            } 
            elseif ($request->has('sync_items')) {
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
        if ($order->customer) {
            $order->customer->update([
                'name'              => $info['name'] ?? $order->customer->name,
                'phone_1'           => $info['phone1'] ?? $order->customer->phone_1,
                'phone_2'           => $info['phone2'] ?? $order->customer->phone_2,
                'instagram_profile' => $info['insta'] ?? $order->customer->instagram_profile,
            ]);
        }

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
