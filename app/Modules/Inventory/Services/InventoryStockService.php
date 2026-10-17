<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryStockService
{
    protected $transactionService;

    public function __construct(InventoryTransactionService $transactionService)
    {
        // نعتمد على خدمة الحركات لتسجيل أي حركة تتم في النظام
        $this->transactionService = $transactionService;
    }

    /**
     * إدخال مخزون جديد (Stock In)
     */
    public function stockIn(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItem::findOrFail($data['inventory_item_id']);
            $totalCost = $data['quantity'] * $data['cost_per_unit'];
            
            $unitId = null;

            // إذا كان الصنف يُتتبع كوحدات (مثل توبات القماش)
            if ($item->track_as_units) {
                if (empty($data['code'])) {
                    throw new Exception("يجب تحديد كود التوب/الوحدة (Top Code).");
                }

                $unit = InventoryUnit::create([
                    'inventory_item_id' => $item->id,
                    'code'              => $data['code'],
                    'quantity'          => $data['quantity'],
                    'unit'              => $item->unit, // meter, kg, etc.
                    'status'            => 'available',
                    'cost_per_unit'     => $data['cost_per_unit'],
                    'total_cost'        => $totalCost,
                ]);

                $unitId = $unit->id;
            }

            // تسجيل حركة الدخول (Transaction)
            return $this->transactionService->recordTransaction([
                'inventory_item_id' => $item->id,
                'inventory_unit_id' => $unitId,
                'type'              => 'stock_in',
                'quantity'          => $data['quantity'],
                'unit'              => $item->unit,
                'cost_per_unit'     => $data['cost_per_unit'],
                'total_cost'        => $totalCost,
                'reason'            => $data['reason'] ?? 'stock_in',
                'reference_type'    => $data['reference_type'] ?? null,
                'reference_id'      => $data['reference_id'] ?? null,
                'user_id'           => auth()->id(),
                'notes'             => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * صرف مخزون (Stock Out)
     */
    public function stockOut(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItem::findOrFail($data['inventory_item_id']);
            
            $unitId = null;
            $costPerUnit = 0;
            $totalCost = 0;
            $quantityToIssue = $data['quantity'];

            // إذا كان الصنف يُتتبع كوحدات (يجب صرف التوب بالكامل)
            if ($item->track_as_units) {
                if (empty($data['inventory_unit_id'])) {
                    throw new Exception("يجب تحديد التوب المراد صرفه.");
                }

                $unit = InventoryUnit::where('id', $data['inventory_unit_id'])
                                     ->where('status', 'available')
                                     ->lockForUpdate() // منع التعارض في حالة التزامن
                                     ->firstOrFail();

                // تحديث حالة التوب إلى تم الصرف
                $unit->update(['status' => 'issued']);
                
                $unitId = $unit->id;
                $quantityToIssue = $unit->quantity; // صرف الكمية الفعلية داخل التوب
                $costPerUnit = $unit->cost_per_unit;
                $totalCost = $unit->total_cost;
            } else {
                // إذا كان الصنف لا يُتتبع كوحدات (مثل الإكسسوارات)
                // يجب التحقق من الرصيد المتاح من خلال الحركات أو خدمة منفصلة
                $availableStock = $this->getAvailableStock($item->id);
                
                if ($availableStock < $quantityToIssue) {
                    throw new Exception("الكمية المطلوبة للصرف أكبر من الرصيد المتاح.");
                }

                $costPerUnit = $item->cost_per_unit;
                $totalCost = $quantityToIssue * $costPerUnit;
            }

            // تسجيل حركة الصرف (Transaction)
            return $this->transactionService->recordTransaction([
                'inventory_item_id' => $item->id,
                'inventory_unit_id' => $unitId,
                'type'              => 'stock_out',
                'quantity'          => $quantityToIssue,
                'unit'              => $item->unit,
                'cost_per_unit'     => $costPerUnit,
                'total_cost'        => $totalCost,
                'reason'            => $data['reason'] ?? 'production',
                'reference_type'    => $data['reference_type'] ?? null,
                'reference_id'      => $data['reference_id'] ?? null,
                'user_id'           => auth()->id(),
                'notes'             => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * حساب الرصيد المتاح للأصناف غير المتتبعة كوحدات
     */
    public function getAvailableStock($itemId)
    {
        // حساب الرصيد بجمع الإدخالات وطرح المصروفات
        $stockIn = DB::table('inventory_transactions')
            ->where('inventory_item_id', $itemId)
            ->whereIn('type', ['stock_in', 'return'])
            ->sum('quantity');

        $stockOut = DB::table('inventory_transactions')
            ->where('inventory_item_id', $itemId)
            ->where('type', 'stock_out')
            ->sum('quantity');
            
        $adjustments = DB::table('inventory_transactions')
            ->where('inventory_item_id', $itemId)
            ->where('type', 'adjustment')
            ->sum('quantity'); // قد يكون موجب أو سالب

        return ($stockIn - $stockOut) + $adjustments;
    }
}