<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * ==========================================================================
     * Employees List
     * ==========================================================================
     */
    public function index(Request $request)
    {
        $employees = Employee::query()

            ->when($request->search, function ($query) use ($request) {

                $query->where(function ($q) use ($request) {

                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%")
                        ->orWhere('address', 'like', "%{$request->search}%")
                        ->orWhere('salary_type', 'like', "%{$request->search}%")
                        ->orWhere('monthly_salary', 'like', "%{$request->search}%")
                        ->orWhereDate('hire_date', $request->search);
                });
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.employees.index', compact('employees'));
    }

    /**
     * ==========================================================================
     * Show Employee Details
     * ==========================================================================
     */
    public function show(Employee $employee)
    {
        $logs = \App\Models\ProductionStepLog::with(['order', 'changer'])
            ->where('employee_id', $employee->id)
            ->orderBy('executed_at', 'desc')
            ->get();

        $piecePrices = \App\Models\TailorPiecePrice::where('employee_id', $employee->id)
            ->get()
            ->keyBy('order_id');

        $monthlyData = [];

        foreach ($logs as $log) {
            $date = $log->executed_at ?? $log->created_at;
            $monthKey = $date ? $date->format('F Y') : 'Unknown';

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'logs' => collect(),
                    'orders_completed' => [],
                    'piece_rate_earnings' => 0,
                    'month_name' => $monthKey
                ];
            }

            $monthlyData[$monthKey]['logs']->push($log);

            if (!in_array($log->order_id, $monthlyData[$monthKey]['orders_completed'])) {
                $monthlyData[$monthKey]['orders_completed'][] = $log->order_id;

                if ($employee->salary_type === 'piece_rate' && isset($piecePrices[$log->order_id])) {
                    $monthlyData[$monthKey]['piece_rate_earnings'] += (float) $piecePrices[$log->order_id]->piece_price;
                }
            }
        }

        return view('pages.employees.show', compact('employee', 'monthlyData'));
    }

    /**
     * ==========================================================================
     * Store Employee
     * ==========================================================================
     */
    public function store(Request $request)
    {
        $data = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'phone'),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'hire_date' => [
                'nullable',
                'date',
            ],

            'salary_type' => [
                'required',
                Rule::in([
                    'monthly',
                    'piece_rate'
                ]),
            ],

            'monthly_salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'active' => [
                'required',
                'boolean',
            ],

        ]);

        /*
        لو Piece Rate
        ميبقاش فيه مرتب شهرى
        */

        if ($data['salary_type'] == 'piece_rate') {

            $data['monthly_salary'] = null;
        }

        Employee::create($data);

        return back()->with(
            'success',
            'تم إضافة الموظف بنجاح.'
        );
    }

    /**
     * ==========================================================================
     * Update Employee
     * ==========================================================================
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'phone')
                    ->ignore($employee->id),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'hire_date' => [
                'nullable',
                'date',
            ],

            'salary_type' => [
                'required',
                Rule::in([
                    'monthly',
                    'piece_rate'
                ]),
            ],

            'monthly_salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'active' => [
                'required',
                'boolean',
            ],

        ]);

        if ($data['salary_type'] == 'piece_rate') {

            $data['monthly_salary'] = null;
        }

        $employee->update($data);

        return back()->with(
            'success',
            'تم تحديث بيانات الموظف بنجاح.'
        );
    }

    /**
     * ==========================================================================
     * Delete Employee
     * ==========================================================================
     */
    public function destroy(Employee $employee)
    {
        /*
        لو الموظف مرتبط بإنتاج
        */

        if (
            $employee->productionAssignments()->exists()
            || $employee->productionStepLogs()->exists()
            || $employee->tailorPiecePrices()->exists()
        ) {

            return back()->withErrors([

                'error' =>
                'لا يمكن حذف الموظف لأنه مرتبط ببيانات إنتاج.'
            ]);
        }

        $employee->delete();

        return back()->with(
            'success',
            'تم حذف الموظف بنجاح.'
        );
    }
}
