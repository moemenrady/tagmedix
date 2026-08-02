<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionTask;
use Illuminate\Http\Request;

class ProductionTaskController extends Controller{
    public function index()
    {
        return ProductionTask::with([
            'order',
            'supervisor',
            'admin'
        ])->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'order_id' => 'required|exists:orders,id',

            'assigned_to' => 'required|exists:users,id',

            'assigned_by' => 'required|exists:users,id',

            'title' => 'required|string|max:255',

            'instruction' => 'nullable|string',

            'required_date' => 'nullable|date',

            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $task = ProductionTask::create($data);

        return response()->json($task, 201);
    }

    public function show(ProductionTask $productionTask)
    {
        return $productionTask->load([
            'order',
            'supervisor',
            'admin'
        ]);
    }

    public function update(Request $request, ProductionTask $productionTask)
    {
        $data = $request->validate([
            'assigned_to' => 'sometimes|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'instruction' => 'nullable|string',
            'required_date' => 'nullable|date',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
        ]);
        if (
            isset($data['status']) &&
            $data['status'] == 'completed'
        ) {
            $data['completed_at'] = now();
        }
        $productionTask->update($data);
        return response()->json($productionTask);
    }

    public function destroy(ProductionTask $productionTask)
    {
        $productionTask->delete();

        return response()->json([
            'message' => 'Deleted Successfully'
        ]);
    }

    public function changeStatus(Request $request, ProductionTask $productionTask)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);
        $productionTask->status = $data['status'];
        if ($data['status'] == 'completed') {
            $productionTask->completed_at = now();
        } else {
            $productionTask->completed_at = null;
        }
        $productionTask->save();
        return response()->json($productionTask);
    }
}
