<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionHistory;
use Illuminate\Http\Request;

class ProductionHistoryController extends Controller
{
    public function index()
    {
        return ProductionHistory::with([
            'order',
            'user'
        ])->latest('executed_at')->paginate(50);
    }

    public function show(ProductionHistory $productionHistory)
    {
        return $productionHistory->load([
            'order',
            'user'
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'order_id' => 'required|exists:orders,id',

            'user_id' => 'required|exists:users,id',

            'action' => 'required|string|max:255',

            'old_value' => 'nullable|array',

            'new_value' => 'nullable|array',

            'ip' => 'nullable|ip',

            'device' => 'nullable|string|max:255',

            'executed_at' => 'required|date',
        ]);

        $history = ProductionHistory::create($data);

        return response()->json($history, 201);
    }
}
