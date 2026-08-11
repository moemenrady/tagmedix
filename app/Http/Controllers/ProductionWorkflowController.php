<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\Production;
use App\Models\ProductionAssignment;
use App\Models\ProductionHistory;
use App\Models\ProductionStepLog;
use App\Models\ProductionTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionWorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // 1. حساب إحصائيات المهام (للكبسولات العلوية)
        $taskQuery = ProductionTask::query();

        // إذا كان المستخدم مشرفاً، نعرض له مهامه فقط
        if ($user->isSupervisor()) {
            $taskQuery->where('assigned_to', $user->id);
        }

        $lateTasks = (clone $taskQuery)->whereDate('required_date', '<', today())
            ->where('status', '!=', 'completed')->count();

        $todayTasks = (clone $taskQuery)->whereDate('required_date', today())
            ->where('status', '!=', 'completed')->count();

        $futureTasks = (clone $taskQuery)->whereDate('required_date', '>', today())
            ->where('status', '!=', 'completed')->count();

        // 2. جلب الأوردرات وتصنيفها حسب المرحلة
        $orderQuery = Order::with(['items', 'productionTasks' => function ($q) {
            $q->latest(); // لنجلب أحدث تكليف
        }, 'productionTasks.supervisor']);

        $orderQuery = Order::with([
            'items',
            'productionTasks' => function ($q) {
                $q->latest();
            },
            'productionTasks.supervisor',
        ]);
        $orders = $orderQuery
            ->whereIn('status', [
                'new',
                'cutting',
                'sewing',
                'ironing',
                'finishing',
                'ready-to-ship'
            ])
            ->get();

        if ($user->isSupervisor()) {

            $orders->each(function ($order) use ($user) {

                $task = $order->productionTasks->sortByDesc('created_at')->first();

                $order->is_my_order =
                    $task &&
                    $task->assigned_to == $user->id;
            });
        } else {

            $orders->each(function ($order) {
                $order->is_my_order = true;
            });
        }
        // جلب الأوردرات في مراحل الإنتاج النشطة
        $orders = $orderQuery->whereIn('status', [
            'new',
            'cutting',
            'sewing',
            'ironing',
            'finishing',
            'ready-to-ship'
        ])->get();

        // تجميع الأوردرات بناءً على الحالة (Status)
        $ordersByStage = $orders->groupBy('status');

        // إعداد مصفوفة لعدد الأوردرات في كل مرحلة
        $stageCounts = [];
        foreach (['new', 'cutting', 'sewing', 'ironing', 'finishing', 'ready-to-ship'] as $stage) {
            $stageCounts[$stage] = isset($ordersByStage[$stage]) ? $ordersByStage[$stage]->count() : 0;
        }

        // 3. تجهيز بيانات الإدارة (للأدمن فقط)
        $supervisors = collect();
        $unassignedOrders = collect();

        if ($user->isAdmin()) {
            $supervisors = User::where('role', 'supervisor')->get();
            // جلب الأوردرات التي لم يتم تكليف أحد بها بعد
            $unassignedOrders = Order::whereDoesntHave('productionTasks')->get();
        }

        // 4. جلب الموظفين الفنيين (لتوزيع المهام في الـ Kanban Modal)
        $employees = Employee::where('active', true)->get();

        return view('pages.production_workflow.index', compact(
            'lateTasks',
            'todayTasks',
            'futureTasks',
            'ordersByStage',
            'stageCounts',
            'supervisors',
            'unassignedOrders',
            'employees'
        ));
    }
    public function assignTasks(Request $request)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'required_date' => 'required|date',
            'priority' => 'required',
            'instruction' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->order_ids as $orderId) {

                $order = Order::findOrFail($orderId);

                ProductionTask::create([

                    'order_id'      => $order->id,

                    'assigned_to'   => $request->supervisor_id,

                    'assigned_by'   => auth()->id(),

                    'title'         => "Production Order {$order->order_number}",

                    'instruction'   => $request->instruction,

                    'required_date' => $request->required_date,

                    'priority'      => $request->priority,

                    'status'        => 'pending',

                ]);

                ProductionHistory::create([

                    'order_id' => $order->id,

                    'user_id' => auth()->id(),

                    'action' => 'assign_supervisor',

                    'old_value' => null,

                    'new_value' => [
                        'supervisor' => $request->supervisor_id
                    ],

                    'ip' => $request->ip(),

                    'device' => $request->userAgent(),

                    'executed_at' => now(),

                ]);
            }
        });

        return back()->with('success', 'تم إصدار أوامر التشغيل.');
    }

    public function moveStatus(Request $request, Order $order)
    {
        $request->validate([

            'to_status' => [
                'required',
                'in:new,cutting,sewing,ironing,finishing,ready-to-ship'
            ],

            'employee_id' => 'nullable|exists:employees,id',

            'executed_at' => 'required|date',

            'note' => 'nullable|string|max:1000',

        ]);

        $user = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */



        DB::transaction(function () use ($request, $order, $user) {

            $oldStatus = $order->status;

            /*
        |--------------------------------------------------------------------------
        | Update Order
        |--------------------------------------------------------------------------
        */

            $order->update([
                'status' => $request->to_status,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Update Production Task
        |--------------------------------------------------------------------------
        */

            $task = ProductionTask::where('order_id', $order->id)
                ->latest()
                ->first();

            if ($task) {

                $status = "in_progress";

                if ($request->to_status == "ready-to-ship") {
                    $status = "completed";
                }

                $task->update([

                    'status' => $status,

                    'completed_at' => $status == "completed"
                        ? now()
                        : null,

                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Production Step Log
        |--------------------------------------------------------------------------
        */

            ProductionStepLog::create([

                'order_id' => $order->id,

                'from_status' => $oldStatus,

                'to_status' => $request->to_status,

                'changed_by' => $user->id,

                'employee_id' => $request->employee_id,

                'note' => $request->note,

                'executed_at' => $request->executed_at,

            ]);

            /*
        |--------------------------------------------------------------------------
        | Production Assignment
        |--------------------------------------------------------------------------
        */

            if (
                in_array($request->to_status, [
                    'cutting',
                    'sewing',
                    'ironing'
                ])
            ) {

                ProductionAssignment::updateOrCreate(

                    [

                        'order_id' => $order->id,

                        'stage' => $request->to_status,

                    ],

                    [

                        'employee_id' => $request->employee_id,

                        'assigned_by' => $user->id,

                        'assigned_at' => $request->executed_at,

                    ]

                );
            }

            /*
        |--------------------------------------------------------------------------
        | Production History
        |--------------------------------------------------------------------------
        */

            ProductionHistory::create([

                'order_id' => $order->id,

                'user_id' => $user->id,

                'action' => 'move_status',

                'old_value' => [

                    'status' => $oldStatus,

                ],

                'new_value' => [

                    'status' => $request->to_status,

                    'employee_id' => $request->employee_id,

                ],

                'ip' => request()->ip(),

                'device' => request()->userAgent(),

                'executed_at' => now(),

            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم نقل الأوردر بنجاح.',
            'user' => $user->name,
            'order' => $order->order_number,
            'stage' => $request->to_status,
        ]);
    }
    public function getHistory(Order $order)
    {
        return response()->json(

            $order->histories()

                ->with('user')

                ->latest()

                ->get()

        );
    }
}
