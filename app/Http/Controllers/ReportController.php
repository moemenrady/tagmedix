<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $status = $request->get('status');
        $governorate = $request->get('governorate');
        $salaryType = $request->get('salary_type', 'all');

        // Resolve date boundaries based on period preset
        if ($period === 'today') {
            $fromDate = Carbon::today()->toDateString();
            $toDate = Carbon::today()->toDateString();
        } elseif ($period === 'this_week') {
            $fromDate = Carbon::now()->startOfWeek()->toDateString();
            $toDate = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($period === 'this_month') {
            $fromDate = Carbon::now()->startOfMonth()->toDateString();
            $toDate = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($period === 'last_30_days') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
            $toDate = Carbon::now()->toDateString();
        }

        // Base Orders Query with applied filters
        $ordersQuery = DB::table('orders');
        if ($fromDate) {
            $ordersQuery->whereDate('orders.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $ordersQuery->whereDate('orders.created_at', '<=', $toDate);
        }
        if ($status && $status !== 'all') {
            $ordersQuery->where('orders.status', $status);
        }
        if ($governorate && $governorate !== 'all') {
            $ordersQuery->join('customer_order_addresses as addr_filter', 'orders.id', '=', 'addr_filter.order_id')
                ->where('addr_filter.governorate', $governorate);
        }

        // Filtered Order IDs for child table calculations
        $filteredOrderIds = (clone $ordersQuery)->pluck('orders.id')->toArray();

        // 1. Core KPIs
        $totalOrders = count($filteredOrderIds);
        $totalRevenue = (clone $ordersQuery)->sum('orders.total_price') ?: 0;
        $avgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;
        $totalShippingDue = (clone $ordersQuery)->sum('orders.shipping_due_amount') ?: 0;

        // Delivered Revenue vs In-Pipeline Revenue
        $deliveredRevenue = (clone $ordersQuery)->whereIn('orders.status', ['shipped', 'delivered'])->sum('orders.total_price') ?: 0;
        $pipelineRevenue = (clone $ordersQuery)->whereNotIn('orders.status', ['shipped', 'delivered', 'canceled'])->sum('orders.total_price') ?: 0;
        $deliveredRevenueRate = $totalRevenue > 0 ? round(($deliveredRevenue / $totalRevenue) * 100, 1) : 0;

        // Cancellation rate
        $canceledOrdersCount = (clone $ordersQuery)->where('orders.status', 'canceled')->count();
        $cancellationRate = $totalOrders > 0 ? round(($canceledOrdersCount / $totalOrders) * 100, 1) : 0;

        // Advances collected
        $paymentsQuery = DB::table('payments');
        if (!empty($filteredOrderIds)) {
            $paymentsQuery->whereIn('order_id', $filteredOrderIds);
        } elseif ($totalOrders === 0 && ($fromDate || $toDate || $status || $governorate)) {
            $paymentsQuery->whereRaw('1 = 0');
        }
        $totalPaidAdvances = $paymentsQuery->sum('amount') ?: 0;
        $remainingDue = max(0, $totalRevenue - $totalPaidAdvances);
        $advanceCollectionRate = $totalRevenue > 0 ? round(($totalPaidAdvances / $totalRevenue) * 100, 1) : 0;

        // Pieces sold
        $itemsQuery = DB::table('order_items');
        if (!empty($filteredOrderIds)) {
            $itemsQuery->whereIn('order_id', $filteredOrderIds);
        } elseif ($totalOrders === 0 && ($fromDate || $toDate || $status || $governorate)) {
            $itemsQuery->whereRaw('1 = 0');
        }
        $totalPiecesSold = $itemsQuery->sum('quantity') ?: 0;

        // Shipments
        $shipmentsQuery = DB::table('shipments');
        if ($fromDate) {
            $shipmentsQuery->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $shipmentsQuery->whereDate('created_at', '<=', $toDate);
        }
        $totalShipments = (clone $shipmentsQuery)->count();
        $totalShipmentAmount = (clone $shipmentsQuery)->sum('amount') ?: 0;

        // Customers & Employees
        $totalCustomers = DB::table('customers')->count();
        $activeEmployees = DB::table('employees')->where('active', 1)->count();
        $pieceRateEmployees = DB::table('employees')->where('active', 1)->where('salary_type', 'piece_rate')->count();
        $monthlyEmployees = DB::table('employees')->where('active', 1)->where('salary_type', 'monthly')->count();

        // 2. Production Lead Time / Cycle Time (Average duration from first step to final completion)
        $leadTimeQuery = DB::table(DB::raw('(SELECT order_id, TIMESTAMPDIFF(HOUR, MIN(executed_at), MAX(executed_at)) as duration_hours FROM production_step_logs WHERE executed_at IS NOT NULL GROUP BY order_id HAVING duration_hours > 0) as lead_sub'));
        if (!empty($filteredOrderIds)) {
            $leadTimeQuery->whereIn('order_id', $filteredOrderIds);
        }
        $avgLeadTimeHours = round($leadTimeQuery->avg('duration_hours') ?: 0, 1);
        $avgLeadTimeDays = round($avgLeadTimeHours / 24, 1);

        // 3. Order Status Breakdown
        $statusCounts = (clone $ordersQuery)
            ->select('orders.status', DB::raw('COUNT(*) as count'), DB::raw('SUM(orders.total_price) as revenue'))
            ->groupBy('orders.status')
            ->get();

        // 4. Sales Timeline (Daily grouping)
        $salesTimeline = (clone $ordersQuery)
            ->select(
                DB::raw('DATE(orders.created_at) as order_date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(orders.total_price) as daily_revenue')
            )
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        // 5. Top Selling Styles / Products
        $stylesQuery = DB::table('order_items')
            ->leftJoin('styles', 'order_items.style_id', '=', 'styles.id')
            ->select(
                DB::raw("COALESCE(styles.name, 'غير محدد') as style_name"),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_sales'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count')
            );
        if (!empty($filteredOrderIds)) {
            $stylesQuery->whereIn('order_items.order_id', $filteredOrderIds);
        } elseif ($totalOrders === 0 && ($fromDate || $toDate || $status || $governorate)) {
            $stylesQuery->whereRaw('1 = 0');
        }
        $topStyles = $stylesQuery
            ->groupBy('style_name')
            ->orderByDesc('total_quantity')
            ->get();

        // 6. Sizes Breakdown
        $sizesQuery = DB::table('order_items')
            ->leftJoin('sizes', 'order_items.size_id', '=', 'sizes.id')
            ->select(
                DB::raw("COALESCE(sizes.name, 'غير محدد') as size_name"),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_sales')
            );
        if (!empty($filteredOrderIds)) {
            $sizesQuery->whereIn('order_items.order_id', $filteredOrderIds);
        } elseif ($totalOrders === 0 && ($fromDate || $toDate || $status || $governorate)) {
            $sizesQuery->whereRaw('1 = 0');
        }
        $topSizes = $sizesQuery
            ->groupBy('size_name')
            ->orderByDesc('total_quantity')
            ->get();

        // 7. Colors Breakdown
        $colorsQuery = DB::table('order_items')
            ->leftJoin('colors', 'order_items.color_id', '=', 'colors.id')
            ->select(
                DB::raw("COALESCE(colors.name, 'غير محدد') as color_name"),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_sales')
            );
        if (!empty($filteredOrderIds)) {
            $colorsQuery->whereIn('order_items.order_id', $filteredOrderIds);
        } elseif ($totalOrders === 0 && ($fromDate || $toDate || $status || $governorate)) {
            $colorsQuery->whereRaw('1 = 0');
        }
        $topColors = $colorsQuery
            ->groupBy('color_name')
            ->orderByDesc('total_quantity')
            ->get();

        // 8. Geographic Breakdown (Governorates)
        $govQuery = DB::table('customer_order_addresses')
            ->join('orders', 'customer_order_addresses.order_id', '=', 'orders.id')
            ->select(
                'customer_order_addresses.governorate',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total_price) as total_sales')
            )
            ->whereNotNull('customer_order_addresses.governorate');
        if ($fromDate) {
            $govQuery->whereDate('orders.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $govQuery->whereDate('orders.created_at', '<=', $toDate);
        }
        if ($status && $status !== 'all') {
            $govQuery->where('orders.status', $status);
        }
        $topGovernorates = $govQuery
            ->groupBy('customer_order_addresses.governorate')
            ->orderByDesc('orders_count')
            ->get();

        // 9. Production & Employee Workload (with salary_type filter support)
        $empWorkloadQuery = DB::table('production_assignments')
            ->join('employees', 'production_assignments.employee_id', '=', 'employees.id')
            ->select(
                'employees.id',
                'employees.name',
                'employees.salary_type',
                'employees.monthly_salary',
                'employees.phone',
                DB::raw('COUNT(production_assignments.id) as total_tasks'),
                DB::raw("SUM(CASE WHEN production_assignments.stage = 'cutting' THEN 1 ELSE 0 END) as cutting_tasks"),
                DB::raw("SUM(CASE WHEN production_assignments.stage = 'sewing' THEN 1 ELSE 0 END) as sewing_tasks"),
                DB::raw("SUM(CASE WHEN production_assignments.stage = 'finishing' THEN 1 ELSE 0 END) as finishing_tasks"),
                DB::raw("SUM(CASE WHEN production_assignments.stage = 'ready-to-ship' THEN 1 ELSE 0 END) as ready_tasks")
            );
        if ($salaryType && $salaryType !== 'all') {
            $empWorkloadQuery->where('employees.salary_type', $salaryType);
        }
        $employeeWorkload = $empWorkloadQuery
            ->groupBy('employees.id', 'employees.name', 'employees.salary_type', 'employees.monthly_salary', 'employees.phone')
            ->orderByDesc('total_tasks')
            ->get();

        // 10. Detailed Financials & Orders Table
        $financialOrdersQuery = DB::table('orders')
            ->leftJoin(DB::raw('(SELECT order_id, SUM(amount) as paid_sum FROM payments GROUP BY order_id) as p'), 'orders.id', '=', 'p.order_id')
            ->leftJoin('customer_order_addresses', 'orders.id', '=', 'customer_order_addresses.order_id')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.customer_name',
                'orders.customer_phone_1',
                'orders.total_price',
                'orders.shipping_due_amount',
                'orders.status',
                'orders.created_at',
                'customer_order_addresses.governorate',
                DB::raw('COALESCE(p.paid_sum, 0) as paid_amount'),
                DB::raw('(orders.total_price - COALESCE(p.paid_sum, 0)) as remaining_amount')
            );
        if ($fromDate) {
            $financialOrdersQuery->whereDate('orders.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $financialOrdersQuery->whereDate('orders.created_at', '<=', $toDate);
        }
        if ($status && $status !== 'all') {
            $financialOrdersQuery->where('orders.status', $status);
        }
        if ($governorate && $governorate !== 'all') {
            $financialOrdersQuery->where('customer_order_addresses.governorate', $governorate);
        }
        $financialOrders = $financialOrdersQuery
            ->orderByDesc('orders.id')
            ->limit(100)
            ->get();

        // 11. Turbo Shipments List
        $turboShipmentsList = (clone $shipmentsQuery)
            ->select('id', 'remote_order_id', 'tracking_number', 'receiver', 'phone', 'government', 'area', 'address', 'amount', 'status', 'created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        // Handle CSV Export
        if ($request->get('export') === 'csv') {
            return $this->exportCsv(
                $request->get('export_type', 'orders'),
                $financialOrders,
                $topStyles,
                $topGovernorates,
                $employeeWorkload,
                $topSizes,
                $topColors,
                $turboShipmentsList
            );
        }

        // Available filter options
        $allStatuses = ['new', 'cutting', 'sewing', 'finishing', 'ready-to-ship', 'shipped', 'delivered', 'canceled'];
        $allGovernorates = DB::table('customer_order_addresses')
            ->select('governorate')
            ->whereNotNull('governorate')
            ->distinct()
            ->orderBy('governorate')
            ->pluck('governorate')
            ->filter()
            ->values();

        return view('pages.reports.index', [
            'kpi' => [
                'totalRevenue' => $totalRevenue,
                'totalOrders' => $totalOrders,
                'avgOrderValue' => $avgOrderValue,
                'totalPiecesSold' => $totalPiecesSold,
                'totalShippingDue' => $totalShippingDue,
                'deliveredRevenue' => $deliveredRevenue,
                'pipelineRevenue' => $pipelineRevenue,
                'deliveredRevenueRate' => $deliveredRevenueRate,
                'cancellationRate' => $cancellationRate,
                'canceledOrdersCount' => $canceledOrdersCount,
                'avgLeadTimeHours' => $avgLeadTimeHours,
                'avgLeadTimeDays' => $avgLeadTimeDays,
                'totalPaidAdvances' => $totalPaidAdvances,
                'remainingDue' => $remainingDue,
                'advanceCollectionRate' => $advanceCollectionRate,
                'totalShipments' => $totalShipments,
                'totalShipmentAmount' => $totalShipmentAmount,
                'totalCustomers' => $totalCustomers,
                'activeEmployees' => $activeEmployees,
                'pieceRateEmployees' => $pieceRateEmployees,
                'monthlyEmployees' => $monthlyEmployees,
            ],
            'statusCounts' => $statusCounts,
            'salesTimeline' => $salesTimeline,
            'topStyles' => $topStyles,
            'topSizes' => $topSizes,
            'topColors' => $topColors,
            'topGovernorates' => $topGovernorates,
            'employeeWorkload' => $employeeWorkload,
            'financialOrders' => $financialOrders,
            'turboShipmentsList' => $turboShipmentsList,
            'allStatuses' => $allStatuses,
            'allGovernorates' => $allGovernorates,
            'filters' => [
                'period' => $period,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'status' => $status,
                'governorate' => $governorate,
                'salary_type' => $salaryType,
            ],
        ]);
    }

    /**
     * Export data to CSV with UTF-8 BOM for Arabic compatibility in Excel.
     */
    protected function exportCsv($type, $orders, $styles, $governorates, $employees, $sizes = [], $colors = [], $shipments = []): StreamedResponse
    {
        $filename = 'tagmedix_report_' . $type . '_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($type, $orders, $styles, $governorates, $employees, $sizes, $colors, $shipments) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel Arabic display
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($type === 'styles') {
                fputcsv($handle, ['الموديل / التصميم', 'إجمالي القطع المباعة', 'إجمالي المبيعات (ج.م)', 'عدد الطلبات', 'متوسط سعر القطعة']);
                foreach ($styles as $s) {
                    $avgPrice = $s->total_quantity > 0 ? round($s->total_sales / $s->total_quantity, 2) : 0;
                    fputcsv($handle, [$s->style_name, $s->total_quantity, $s->total_sales, $s->orders_count, $avgPrice]);
                }
            } elseif ($type === 'sizes_colors') {
                fputcsv($handle, ['نوع الخاصية', 'القيمة / الاسم', 'إجمالي القطع المباعة', 'إجمالي المبيعات (ج.م)']);
                foreach ($sizes as $sz) {
                    fputcsv($handle, ['المقاس (Size)', $sz->size_name, $sz->total_quantity, $sz->total_sales]);
                }
                foreach ($colors as $cl) {
                    fputcsv($handle, ['اللون (Color)', $cl->color_name, $cl->total_quantity, $cl->total_sales]);
                }
            } elseif ($type === 'governorates') {
                fputcsv($handle, ['المحافظة', 'عدد الطلبات', 'إجمالي قيمة الطلبات (ج.م)', 'متوسط قيمة الطلب']);
                foreach ($governorates as $g) {
                    $avgGov = $g->orders_count > 0 ? round($g->total_sales / $g->orders_count, 2) : 0;
                    fputcsv($handle, [$g->governorate, $g->orders_count, $g->total_sales, $avgGov]);
                }
            } elseif ($type === 'employees') {
                fputcsv($handle, ['اسم الموظف', 'رقم الهاتف', 'طبيعة الراتب', 'إجمالي المهام', 'مرحلة القص', 'مرحلة الخياطة', 'مرحلة التشطيب', 'جاهز للشحن']);
                foreach ($employees as $e) {
                    fputcsv($handle, [
                        $e->name,
                        $e->phone ?? '-',
                        $e->salary_type === 'piece_rate' ? 'بالقطعة' : 'شهري',
                        $e->total_tasks,
                        $e->cutting_tasks,
                        $e->sewing_tasks,
                        $e->finishing_tasks,
                        $e->ready_tasks,
                    ]);
                }
            } elseif ($type === 'turbo_shipments') {
                fputcsv($handle, ['رقم الشحنة الداخلي', 'رقم الطلب', 'رقم التتبع Turbo', 'اسم المستلم', 'رقم الهاتف', 'المحافظة', 'المنطقة', 'العنوان', 'مبلغ التحصيل (ج.م)', 'الحالة', 'تاريخ الإنشاء']);
                foreach ($shipments as $sh) {
                    fputcsv($handle, [
                        $sh->id,
                        $sh->remote_order_id,
                        $sh->tracking_number ?? '-',
                        $sh->receiver,
                        $sh->phone,
                        $sh->government,
                        $sh->area,
                        $sh->address,
                        $sh->amount,
                        $sh->status,
                        $sh->created_at,
                    ]);
                }
            } else {
                fputcsv($handle, ['رقم الطلب', 'اسم العميل', 'رقم الهاتف', 'المحافظة', 'إجمالي الطلب', 'تكلفة الشحن', 'المقدم المدفوع', 'المتبقي', 'حالة الطلب', 'تاريخ الطلب']);
                foreach ($orders as $o) {
                    fputcsv($handle, [
                        $o->order_number,
                        $o->customer_name,
                        $o->customer_phone_1,
                        $o->governorate ?? 'غير محدد',
                        $o->total_price,
                        $o->shipping_due_amount,
                        $o->paid_amount,
                        $o->remaining_amount,
                        $o->status,
                        $o->created_at,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
