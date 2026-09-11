<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}
    public function show(Customer $customer)
    {
        $customer->load([
            'orders' => function ($q) {
                $q->with(['address', 'items.style', 'items.type', 'payments', 'shipments'])->latest();
            },
            'orderAddresses' => function ($q) {
                $q->latest();
            },
        ]);

        $stats = [
            'total_orders' => $customer->orders->count(),
            'total_spent' => $customer->orders->sum('total_price'),
            'total_paid' => $customer->orders->sum(fn($o) => $o->getTotalPaid()),
            'total_remaining' => $customer->orders->sum(fn($o) => $o->getRemainingAmount()),
            'shipped_orders' => $customer->orders->where('status', 'shipped')->count(),
            'delivered_orders' => $customer->orders->where('status', 'delivered')->count(),
        ];

        return view('pages.customers.show', compact('customer', 'stats'));
    }
    public function index(Request $request)
    {
        $query = Customer::query()->with(['orderAddresses' => fn($q) => $q->latest()]);

        // 1. البحث الشامل
        if ($q = $request->get('q')) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('phone_1', 'LIKE', "%{$q}%")
                    ->orWhere('phone_2', 'LIKE', "%{$q}%")
                    ->orWhere('instagram_profile', 'LIKE', "%{$q}%")
                    ->orWhereHas('orderAddresses', function ($addr) use ($q) {
                        $addr->where('governorate', 'LIKE', "%{$q}%")
                            ->orWhere('city', 'LIKE', "%{$q}%")
                            ->orWhere('full_address', 'LIKE', "%{$q}%");
                    })
                    ->orWhereHas('orders', function ($ord) use ($q) {
                        $ord->where('order_number', 'LIKE', "%{$q}%");
                    });
            });
        }

        // 2. فلتر نطاق التاريخ للعملاء الجدد
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // 3. فلتر المحافظة
        if ($gov = $request->get('governorate')) {
            $query->whereHas('orderAddresses', fn($a) => $a->where('governorate', $gov));
        }

        // 4. إحصائيات مع الحساب الإجمالي المسبق (Count & Sum)
        $query->withCount('orders')
            ->withSum('orders', 'total_price');

        // 5. الترتيب
        match ($request->get('sort_by')) {
            'oldest' => $query->orderBy('id', 'asc'),
            'most_orders' => $query->orderBy('orders_count', 'desc'),
            'highest_spent' => $query->orderBy('orders_sum_total_price', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $customers = $query->paginate(15)->withQueryString();

        // 6. تجميع كارت التحليلات فوق
        $statistics = [
            'total_customers' => Customer::count(),
            'new_customers'   => Customer::whereBetween('created_at', [
                $request->get('from') ?? now()->startOfMonth(),
                $request->get('to') ?? now()->endOfDay()
            ])->count(),
            'total_spent'     => \App\Models\Order::sum('total_price'),
            'avg_orders_per_customer' => Customer::count() > 0 ? \App\Models\Order::count() / Customer::count() : 0,
        ];

        $governorates = \App\Models\CustomerOrderAddress::distinct()->pluck('governorate')->filter()->values();

        return view('pages.customers.index', compact('customers', 'statistics', 'governorates'));
    }
    public function searchByPhone(Request $request): JsonResponse
    {
        $phone = $request->query('phone', '');

        $customers = $this->customerService->searchByPhone($phone);

        return response()->json([
            'success' => true,
            'data'    => $customers,
        ]);
    }
}
