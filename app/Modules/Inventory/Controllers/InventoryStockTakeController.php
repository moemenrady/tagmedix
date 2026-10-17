<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Services\InventoryAdjustmentService;
use Illuminate\Http\Request;
use Exception;

class InventoryStockTakeController extends Controller
{
    protected $adjustmentService;

    public function __construct(InventoryAdjustmentService $adjustmentService)
    {
        // الاعتماد على الخدمة لتنفيذ المنطق البرمجي الخاص بتسويات الجرد
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * عرض واجهة الجرد
     */
    public function index()
    {
        // جلب الأصناف الفعالة مع وحداتها المتاحة (لتبسيط اختيار التوبات في الواجهة)
        $items = InventoryItem::with(['units' => function ($query) {
            $query->where('status', 'available');
        }])->where('is_active', true)->get();

        return view('inventory.stock.take', compact('items'));
    }

    /**
     * معالجة جرد وتسوية الأصناف غير المتتبعة بوحدات (مثل الإكسسوارات)
     */
    public function adjustNonTrackable(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'actual_stock'      => 'required|numeric|min:0', // لا يمكن أن يكون الرصيد الفعلي أقل من صفر
            'reason'            => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        try {
            $transaction = $this->adjustmentService->adjustNonTrackableStock($validated);

            if ($transaction) {
                return back()->with('success', 'Stock adjusted successfully. Difference recorded.');
            }

            return back()->with('info', 'No adjustment needed. Actual stock matches system stock.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * معالجة جرد وتسوية الوحدات (مثل توبات القماش)
     */
    public function adjustTrackable(Request $request)
    {
        $validated = $request->validate([
            'inventory_unit_id' => 'required|exists:inventory_units,id',
            'actual_quantity'   => 'required|numeric|min:0', // في حالة فقدان التوب، يتم إدخال 0
            'reason'            => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        try {
            $transaction = $this->adjustmentService->adjustTrackableUnit($validated);

            if ($transaction) {
                return back()->with('success', 'Unit adjusted successfully. Difference recorded.');
            }

            return back()->with('info', 'No adjustment needed. Actual quantity matches system quantity.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}