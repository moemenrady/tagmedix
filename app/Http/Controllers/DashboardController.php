<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard with optimized real database metrics,
     * searchable orders table with pagination, and memory-safe interactive staff Kanban.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $viewMode = $request->get('view', 'both'); // 'both', 'admin', 'staff'

        // 1. Live Stat Cards Metrics (Fast & Optimized)
        $totalOrders = Order::count();
        $thisMonthOrders = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $lastMonthOrders = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $orderTrendPct = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : ($thisMonthOrders > 0 ? 100 : 0);

        // Financial balances
        $totalRevenue = Order::sum('total_price') ?: 0;
        $totalPaid = Payment::sum('amount') ?: 0;
        $pendingPayments = max(0, $totalRevenue - $totalPaid);

        // Fast Pending Orders Count (correlated subquery instead of heavy unindexed join)
        $pendingOrdersCount = Order::whereRaw('(total_price > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id))')->count();

        // Production pipeline counts
        $inProductionCount = Order::whereIn('status', ['cutting', 'sewing', 'finishing'])->count();
        $inProductionPct = $totalOrders > 0 ? round(($inProductionCount / $totalOrders) * 100, 1) : 0;

        $readyToShipCount = Order::where('status', 'ready-to-ship')->count();

        $shippedCount = Order::whereIn('status', ['shipped', 'delivered'])->count();
        $deliveredToday = Order::whereIn('status', ['shipped', 'delivered'])
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // 2. ADMIN VIEW: Orders Query with relationships, search, and filters
        $adminOrdersQuery = Order::with([
            'customer',
            'address',
            'items.style',
            'items.color',
            'items.size',
            'payments',
        ]);

        if (!empty($search)) {
            $adminOrdersQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone_1', 'like', "%{$search}%")
                    ->orWhere('customer_phone_2', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone_1', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $adminOrdersQuery->where('status', $statusFilter);
        }

        $adminOrders = $adminOrdersQuery->latest('id')->paginate(15)->withQueryString();

        // 3. STAFF VIEW: Memory-Safe Kanban Board (Per-Column Database Limits)
        $kanbanColumns = [
            'new' => [
                'title' => 'New / جديد',
                'next_status' => 'cutting',
                'next_label' => 'بدء القص (Cutting)',
            ],
            'cutting' => [
                'title' => 'Cutting / قص',
                'next_status' => 'sewing',
                'next_label' => 'تحويل للخياطة (Sewing)',
            ],
            'sewing' => [
                'title' => 'Sewing / خياطة',
                'next_status' => 'finishing',
                'next_label' => 'تحويل للتشطيب (Finishing)',
            ],
            'finishing' => [
                'title' => 'Finishing / تشطيب',
                'next_status' => 'ready-to-ship',
                'next_label' => 'جاهز للشحن (Packing)',
            ],
            'ready-to-ship' => [
                'title' => 'Ready to Ship / جاهز للشحن',
                'next_status' => 'shipped',
                'next_label' => 'تسليم للمندوب (Shipped)',
            ],
            'shipped' => [
                'title' => 'Shipped / تم الشحن',
                'next_status' => 'delivered',
                'next_label' => 'تأكيد التسليم (Delivered)',
            ],
        ];

        $kanbanOrdersByStatus = [];
        $kanbanTotalCounts = [];

        foreach ($kanbanColumns as $colKey => $colData) {
            $colQuery = Order::with([
                'customer',
                'items.style',
                'items.color',
                'items.size',
                'assignments.employee',
            ])->where('status', $colKey);

            if (!empty($search)) {
                $colQuery->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone_1', 'like', "%{$search}%")
                        ->orWhere('customer_phone_2', 'like', "%{$search}%");
                });
            }

            // Record total count in database for column badge
            $kanbanTotalCounts[$colKey] = (clone $colQuery)->count();

            // Strictly limit rows in SQL to prevent memory exhaustion
            $colLimit = ($colKey === 'shipped') ? 15 : 30;
            $kanbanOrdersByStatus[$colKey] = $colQuery->latest('id')->limit($colLimit)->get();
        }

        // Active employees for reference
        $activeEmployees = Employee::where('active', 1)->orderBy('name')->get();

        return view('pages.dashboard', [
            'stats' => [
                'totalOrders' => $totalOrders,
                'thisMonthOrders' => $thisMonthOrders,
                'orderTrendPct' => $orderTrendPct,
                'pendingPayments' => $pendingPayments,
                'pendingOrdersCount' => $pendingOrdersCount,
                'inProductionCount' => $inProductionCount,
                'inProductionPct' => $inProductionPct,
                'readyToShipCount' => $readyToShipCount,
                'shippedCount' => $shippedCount,
                'deliveredToday' => $deliveredToday,
            ],
            'adminOrders' => $adminOrders,
            'kanbanColumns' => $kanbanColumns,
            'kanbanOrdersByStatus' => $kanbanOrdersByStatus,
            'kanbanTotalCounts' => $kanbanTotalCounts,
            'activeEmployees' => $activeEmployees,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'view' => $viewMode,
            ],
        ]);
    }
}
