<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryTransactionController extends Controller
{
    /**
     * عرض حركات المخزون
     */
    public function index(Request $request)
    {
        $transactions = InventoryTransaction::with([
            'item:id,name,code,unit',
            'creator:id,name',
        ])
            ->when($request->item_id, function ($query, $itemId) {
                $query->where('item_id', $itemId);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->reference_type, function ($query, $referenceType) {
                $query->where('reference_type', $referenceType);
            })
            ->when($request->created_by, function ($query, $createdBy) {
                $query->where('created_by', $createdBy);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(
                min(
                    $request->integer('per_page', 15),
                    100
                )
            )
            ->withQueryString();

        return view(
            'pages.inventory.transactions.index',
            compact('transactions')
        );
    }

    /**
     * عرض حركة مخزون واحدة
     */
    public function show(
        InventoryTransaction $inventoryTransaction
    ): JsonResponse {
        $inventoryTransaction->load([
            'item:id,name,code,unit',
            'creator:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل حركة المخزون بنجاح',
            'data' => $inventoryTransaction,
        ]);
    }
}
