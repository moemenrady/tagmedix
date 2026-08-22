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
            'items',
            'payments.creator',
        ])
            ->withSum('payments', 'amount')
            ->findOrFail($id);

        return view('pages.orders.show', compact('order'));
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
        $order = Order::with([
            'items',
            'payments',
        ])->withSum('payments', 'amount')->findOrFail($id);

        // --- 1. التعامل مع طلبات التحديث الجزئية الذكية (AJAX / JSON) ---
        if ($request->wantsJson() || $request->ajax()) {
            DB::beginTransaction();
            try {
                // أ. تحديث حالة الطلب
                if ($request->has('status')) {
                    $order->update(['status' => $request->status]);
                }

                // ب. تحديث المبلغ المدفوع

                // ج. تحديث بيانات العميل فقط
                if ($request->has('customer_info')) {
                    $info = $request->customer_info;
                    $order->update([
                        'customer_name' => $info['name'] ?? $order->customer_name,
                        'phone_1' => $info['phone1'] ?? $order->phone_1,
                        'phone_2' => $info['phone2'] ?? $order->phone_2,
                        'governorate' => $info['gov'] ?? $order->governorate,
                        'full_address' => $info['address'] ?? $order->full_address,
                        'instagram_profile' => $info['insta'] ?? $order->instagram_profile,
                    ]);
                }

                // د. تحديث بيانات منتج (Item) واحد وإعادة حساب الإجمالي
                if ($request->has('order_item')) {
                    $itemData = $request->order_item;
                    $item = $order->items()->findOrFail($itemData['id']);

                    $quantity = $itemData['qty'] ?? $item->quantity;
                    $price = $itemData['price'] ?? $item->price;
                    $itemTotal = $quantity * $price;

                    $item->update([
                        'product_name' => $itemData['productName'] ?? $item->product_name,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $itemTotal,
                        'notes' => $itemData['notes'] ?? $item->notes,
                    ]);

                    // إعادة حساب إجماليات الطلب بعد تعديل المنتج
                    $totalPrice = $order->items()->sum('total');
                    $order->update([
                        'total_price' => $totalPrice,
                        'remaining_amount' => $totalPrice - $order->paid_amount,
                    ]);
                }

                DB::commit();

                // إرجاع الطلب المحدث لتحديث الواجهة تلقائياً
                return response()->json([
                    'success' => true,
                    'message' => 'تم التحديث بنجاح',
                    'order' => $order->fresh(['items']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        // --- 2. التعامل مع الرفع التقليدي (Full Form Submit) ---
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone_1' => 'required|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'governorate' => 'required|string|max:255',
            'full_address' => 'required|string',
            'instagram_profile' => 'nullable|url',
            'source' => 'required|in:new,from_stock,replacement',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order->update([
                'customer_name' => $request->customer_name,
                'phone_1' => $request->phone_1,
                'phone_2' => $request->phone_2,
                'governorate' => $request->governorate,
                'full_address' => $request->full_address,
                'instagram_profile' => $request->instagram_profile,
                'paid_amount' => $request->paid_amount ?? 0,
                'source' => $request->source,
            ]);

            $order->items()->delete();

            $totalPrice = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'design' => $item['design'] ?? null,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'sleeve_type_id' => $item['sleeve_type_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                    'notes' => $item['notes'] ?? null,
                ]);
                $totalPrice += $itemTotal;
            }

            $order->update([
                'total_price' => $totalPrice,
                'remaining_amount' => $totalPrice - $order->paid_amount,
            ]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'تم تحديث الطلب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('fail', $e->getMessage());
        }
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
