<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll runs.
     */
    public function index(Request $request)
    {
        $selectedMonth = (int) $request->get('month', date('n'));
        $selectedYear = (int) $request->get('year', date('Y'));

        $query = Payroll::with('employee')
            ->where('period_month', $selectedMonth)
            ->where('period_year', $selectedYear)
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->status, fn($q, $st) => $q->where('status', $st));

        $stats = [
            'total_employees_count' => (clone $query)->count(),
            'total_base_and_pieces' => (clone $query)->select(DB::raw('SUM(base_salary + piece_rate_earnings) as total'))->value('total') ?? 0,
            'total_bonuses' => (clone $query)->sum('bonuses_amount'),
            'total_deductions_and_advances' => (clone $query)->select(DB::raw('SUM(deductions_amount + advances_deducted) as total'))->value('total') ?? 0,
            'total_net_salary' => (clone $query)->sum('net_salary'),
        ];

        $payrolls = $query->orderBy('status')->latest('id')->paginate(20)->withQueryString();
        $employees = Employee::where('active', true)->orderBy('name')->get();

        return view('pages.payroll.index', compact(
            'payrolls',
            'stats',
            'selectedMonth',
            'selectedYear',
            'employees'
        ));
    }

    /**
     * Automatically generate / recalculate payroll for all active employees for a given month/year.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        DB::transaction(function () use ($month, $year) {
            $employees = Employee::where('active', true)->get();

            foreach ($employees as $employee) {
                // If payroll already exists and is marked as PAID, do not overwrite
                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($existingPayroll && $existingPayroll->status === 'paid') {
                    continue;
                }

                $salaryType = $employee->salary_type;
                $baseSalary = 0;
                $pieceRateEarnings = 0;
                $piecesCount = 0;

                if ($salaryType === 'monthly') {
                    $baseSalary = (float) $employee->monthly_salary;
                } else {
                    // Calculate from production logs and tailor piece prices
                    $pieceData = $employee->calculatePieceEarningsForPeriod($month, $year);
                    $pieceRateEarnings = (float) $pieceData['earnings'];
                    $piecesCount = (int) $pieceData['pieces_count'];
                }

                // Calculate unsettled advances, bonuses, and deductions for this employee in this month
                $advances = EmployeeAdvance::where('employee_id', $employee->id)
                    ->where('is_settled', false)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->get();

                $bonusesAmount = (float) $advances->where('type', 'bonus')->sum('amount');
                $deductionsAmount = (float) $advances->where('type', 'deduction')->sum('amount');
                $advancesDeducted = (float) $advances->where('type', 'advance')->sum('amount');

                $gross = ($salaryType === 'monthly' ? $baseSalary : $pieceRateEarnings) + $bonusesAmount;
                $totalDeductions = $deductionsAmount + $advancesDeducted;
                $netSalary = max(0, $gross - $totalDeductions);

                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period_month' => $month,
                        'period_year' => $year,
                    ],
                    [
                        'payroll_number' => $existingPayroll?->payroll_number ?? Payroll::generatePayrollNumber(),
                        'salary_type' => $salaryType,
                        'base_salary' => $baseSalary,
                        'piece_rate_earnings' => $pieceRateEarnings,
                        'completed_pieces_count' => $piecesCount,
                        'bonuses_amount' => $bonusesAmount,
                        'deductions_amount' => $deductionsAmount,
                        'advances_deducted' => $advancesDeducted,
                        'net_salary' => $netSalary,
                        'status' => 'draft',
                        'created_by' => auth()->id(),
                    ]
                );

                // Mark advances as linked to this payroll
                foreach ($advances as $adv) {
                    $adv->update([
                        'payroll_id' => $payroll->id,
                        'is_settled' => true,
                    ]);
                }
            }
        });

        return redirect()->route('payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "تم احتساب وتوليد مسير رواتب شهر {$month}/{$year} لجميع الموظفين بنجاح.");
    }

    /**
     * Display detailed employee payslip for a specific payroll record.
     */
    public function show(Payroll $payroll)
    {
        $payroll->load(['employee', 'advances', 'creator']);

        // Also fetch production logs for this period if piece rate
        $productionLogs = collect();
        if ($payroll->salary_type === 'piece_rate') {
            $productionLogs = $payroll->employee->productionStepLogs()
                ->whereMonth('executed_at', $payroll->period_month)
                ->whereYear('executed_at', $payroll->period_year)
                ->with(['order', 'changer'])
                ->get();
        }

        return view('pages.payroll.show', compact('payroll', 'productionLogs'));
    }

    /**
     * Approve payroll record.
     */
    public function approve(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('info', 'الراتب مدفوع بالفعل.');
        }

        $payroll->update(['status' => 'approved']);

        return back()->with('success', 'تم اعتماد الراتب بنجاح.');
    }

    /**
     * Mark payroll as paid.
     */
    public function pay(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? $payroll->notes,
        ]);

        return back()->with('success', 'تم تأكيد صرف وتسليم الراتب للموظف بنجاح.');
    }

    /**
     * Bulk approve all drafts for selected period.
     */
    public function bulkApprove(Request $request)
    {
        $month = (int) $request->month;
        $year = (int) $request->year;

        Payroll::where('period_month', $month)
            ->where('period_year', $year)
            ->where('status', 'draft')
            ->update(['status' => 'approved']);

        return back()->with('success', 'تم اعتماد جميع رواتب الشهر المحددة بنجاح.');
    }

    /**
     * Bulk pay all approved payrolls for selected period.
     */
    public function bulkPay(Request $request)
    {
        $month = (int) $request->month;
        $year = (int) $request->year;
        $method = $request->get('payment_method', 'cash');

        Payroll::where('period_month', $month)
            ->where('period_year', $year)
            ->where('status', 'approved')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $method,
            ]);

        return back()->with('success', 'تم تسجيل صرف وتسليم جميع الرواتب المعتمدة بنجاح.');
    }

    /**
     * Remove a draft payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'لا يمكن حذف راتب تم صرفه وتسليمه.');
        }

        DB::transaction(function () use ($payroll) {
            // Unlink advances
            EmployeeAdvance::where('payroll_id', $payroll->id)
                ->update(['payroll_id' => null, 'is_settled' => false]);

            $payroll->delete();
        });

        return back()->with('success', 'تم حذف مسير الراتب وإعادة فتح السلف المرتبطة بنجاح.');
    }
}
