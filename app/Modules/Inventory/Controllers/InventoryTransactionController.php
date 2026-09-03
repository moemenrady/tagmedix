<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\InventoryItemVariant;
use App\Modules\Inventory\Services\InventoryStockOutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = \App\Modules\Inventory\Models\InventoryTransaction::with([
            'inventoryItem:id,name,code',
            'creator:id,name',
        ])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(min($request->integer('per_page', 15), 100))
            ->withQueryString();

        return view('pages.inventory.transactions.index', compact('transactions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request, InventoryStockOutService $stockOutService)
    {
        $request->validate([
            'type' => 'required|string',
            'inventory_item_variant_id' => 'required|exists:inventory_item_variants,id',
            'quantity' => 'required|numeric|gt:0',
        ]);

        $variant = InventoryItemVariant::with('inventoryItem')->findOrFail($request->inventory_item_variant_id);

        if ($variant->inventoryItem->stock_tracking_type === 'individual') {
            return redirect()->back()->with('error', 'لا يمكن صرف الأصناف ذات التتبع الفردي (التوبات) من خلال نافذة الصرف السريع. يرجى استخدام صفحة الصرف المخصصة.');
        }

        $data = [
            'inventory_item_id' => $variant->inventory_item_id,
            'inventory_item_variant_id' => $variant->id,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ];

        try {
            $stockOutService->execute($data, auth()->id());
            return redirect()->back()->with('success', 'تم تسجيل الصرف بنجاح.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $inventoryTransaction = \App\Modules\Inventory\Models\InventoryTransaction::with([
            'inventoryItem:id,name,code',
            'creator:id,name',
        ])->findOrFail($id);

        // Map for the frontend to match what it expects
        $data = $inventoryTransaction->toArray();
        $data['item'] = $data['inventory_item'] ?? null;
        if ($data['item'] && !isset($data['item']['unit'])) {
            $data['item']['unit'] = $inventoryTransaction->unit_type;
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل حركة المخزون بنجاح',
            'data' => $data,
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
