<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;

class EmployeeAdvanceController extends Controller
{
    /**
     * Store a new advance, bonus, or deduction for an employee.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'type' => 'required|in:advance,bonus,deduction',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        $validated['employee_id'] = $employee->id;
        $validated['created_by'] = auth()->id();
        $validated['is_settled'] = false;

        EmployeeAdvance::create($validated);

        $labels = [
            'advance' => 'السلفة',
            'bonus' => 'المكافأة',
            'deduction' => 'الخصم',
        ];

        return back()->with('success', 'تم تسجيل ' . ($labels[$validated['type']] ?? 'المعاملة') . ' للموظف بنجاح.');
    }

    /**
     * Remove an unsettled advance/bonus/deduction.
     */
    public function destroy(EmployeeAdvance $advance)
    {
        if ($advance->is_settled) {
            return back()->with('error', 'لا يمكن حذف معاملة تم تسويتها بالفعل في مسير رواتب معتمد.');
        }

        $advance->delete();

        return back()->with('success', 'تم حذف المعاملة بنجاح.');
    }
}
