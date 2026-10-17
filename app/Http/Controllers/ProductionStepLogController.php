<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionStepLog;
use Illuminate\Http\Request;

class ProductionStepLogController extends Controller
{
    public function index()
    {
        return ProductionStepLog::with([
            'order',
            'changer',
            'employee'
        ])
            ->latest('executed_at')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'order_id' => 'required|exists:orders,id',

            'from_status' => 'nullable|string',

            'to_status' => 'required|string',

            'changed_by' => 'required|exists:users,id',

            'employee_id' => 'nullable|exists:users,id',

            'note' => 'nullable|string',

            'executed_at' => 'required|date',
        ]);

        $log = ProductionStepLog::create($data);

        return response()->json($log, 201);
    }

    public function show(ProductionStepLog $productionStepLog)
    {
        return $productionStepLog->load([
            'order',
            'changer',
            'employee'
        ]);
    }

    public function update(Request $request, ProductionStepLog $productionStepLog)
    {
        $data = $request->validate([

            'from_status' => 'nullable|string',

            'to_status' => 'sometimes|string',

            'employee_id' => 'nullable|exists:users,id',

            'note' => 'nullable|string',

            'executed_at' => 'sometimes|date',
        ]);

        $productionStepLog->update($data);

        return response()->json($productionStepLog);
    }

    public function destroy(ProductionStepLog $productionStepLog)
    {
        $productionStepLog->delete();

        return response()->json([
            'message' => 'Deleted Successfully'
        ]);
    }
}
