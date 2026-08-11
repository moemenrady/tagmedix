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
