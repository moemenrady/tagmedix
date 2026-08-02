<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionAssignment;
use Illuminate\Http\Request;

class ProductionAssignmentController extends Controller
{
    public function index()
    {
        return ProductionAssignment::with([
            'order',
            'employee',
            'assignedBy'
        ])->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'order_id' => 'required|exists:orders,id',

            'stage' => 'required|in:cutting,sewing,ironing',

            'employee_id' => 'required|exists:users,id',

            'assigned_by' => 'required|exists:users,id',

            'assigned_at' => 'required|date',
        ]);

        $assignment = ProductionAssignment::create($data);

        return response()->json($assignment, 201);
    }

    public function show(ProductionAssignment $productionAssignment)
    {
        return $productionAssignment->load([
            'order',
            'employee',
            'assignedBy'
        ]);
    }

    public function update(Request $request, ProductionAssignment $productionAssignment)
    {
        $data = $request->validate([

            'employee_id' => 'sometimes|exists:users,id',

            'assigned_at' => 'sometimes|date',
        ]);

        $productionAssignment->update($data);

        return response()->json($productionAssignment);
    }

    public function destroy(ProductionAssignment $productionAssignment)
    {
        $productionAssignment->delete();

        return response()->json([
            'message' => 'Deleted Successfully'
        ]);
    }
}
