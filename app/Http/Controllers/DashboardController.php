<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\ExpenseType;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index'); // الصفحة الأساسية
    }

    public function loadSection($section)
    {
        if ($section == 'capital') {
            $totalCapital = Capital::sum('amount');
            $transactions = Transaction::latest()->get();
            return view('dashboard.partials.capital', compact('totalCapital', 'transactions'))->render();
        }

        if ($section == 'expense-types') {
            $types = ExpenseType::withSum('expenses', 'amount')->get();
            return view('dashboard.partials.expense_types', compact('types'))->render();
        }

        if ($section == 'expenses') {
            $expenseTypes = ExpenseType::with('expenses')->get();
            return view('dashboard.partials.expenses', compact('expenseTypes'))->render();
        }

        if ($section == 'reports') {
            $totalSpent = Expense::sum('amount');

            $topType = ExpenseType::withSum('expenses', 'amount')
                ->orderByDesc('expenses_sum_amount')
                ->first();

            $typesReport = ExpenseType::withCount('expenses')
                ->withSum('expenses', 'amount')
                ->get();

            return view('dashboard.partials.reports', compact('totalSpent', 'topType', 'typesReport'))->render();
        }

        return response()->json(['error' => 'Not Found'], 404);
    }
}
