<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryUnit;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryAdjustmentService
{
    protected $transactionService;
    protected $stockService;

    public function __construct(
        InventoryTransactionService $transactionService,
        InventoryStockService $stockService
    ) {
        $this->transactionService = $transactionService;
        $this->stockService = $stockService;
    }

    /**
     * جرد وتسوية صنف لا يُتتبع بالوحدات (مثل الإكسسوارات)
     */
    public function adjustNonTrackableStock(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItem::findOrFail($data['inventory_item_id']);

            if ($item->track_as_units) {
                throw new Exception("هذا الصنف يُتتبع بالوحدات. يرجى استخدام تسوية الوحدات.");
            }

            // حساب الرصيد الحالي من النظام
            $systemStock = $this->stockService->getAvailableStock($item->id);
            $actualStock = $data['actual_stock'];

            $difference = $actualStock - $systemStock;

            // إذا كان الرصيد الفعلي مطابقاً للنظام، لا داعي للتسوية
            if ($difference == 0) {
                return null; 
            }

            // تسجيل حركة التسوية بالفارق
            return $this->transactionService->recordTransaction([
                'inventory_item_id' => $item->id,
                'inventory_unit_id' => null,
                'type'              => 'adjustment',
                'quantity'          => $difference, // قد يكون بالسالب (عجز) أو بالموجب (زيادة)
                'unit'              => $item->unit,
                'cost_per_unit'     => $item->cost_per_unit,
                'reason'            => $data['reason'] ?? 'inventory_count',
                'user_id'           => auth()->id(),
                'notes'             => $data['notes'] ?? "System Stock: {$systemStock}, Actual: {$actualStock}",
            ]);
        });
    }

    /**
     * جرد وتسوية وحدة فعلية (مثل توب قماش)
     */
    public function adjustTrackableUnit(array $data)
    {
        return DB::transaction(function () use ($data) {
            $unit = InventoryUnit::where('id', $data['inventory_unit_id'])
                                 ->lockForUpdate()
                                 ->firstOrFail();

            $item = $unit->item;
            $systemQuantity = $unit->quantity;
            $actualQuantity = $data['actual_quantity'];

            // في حالة فقدان التوب بالكامل أثناء الجرد (Actual = 0)
            if ($actualQuantity == 0) {
                $difference = -$systemQuantity;
                $unit->update([
                    'quantity'   => 0,
                    'total_cost' => 0,
                    'status'     => 'issued' // إخراجه من المتاح
                ]);
            } else {
                $difference = $actualQuantity - $systemQuantity;
                
                if ($difference == 0) {
                    return null;
                }

                // تحديث كمية التوب وتكلفته الجديدة
                $unit->update([
                    'quantity'   => $actualQuantity,
                    'total_cost' => $actualQuantity * $unit->cost_per_unit,
                ]);
            }

            // تسجيل حركة التسوية على مستوى التوب
            return $this->transactionService->recordTransaction([
                'inventory_item_id' => $item->id,
                'inventory_unit_id' => $unit->id,
                'type'              => 'adjustment',
                'quantity'          => $difference,
                'unit'              => $item->unit,
                'cost_per_unit'     => $unit->cost_per_unit,
                'reason'            => $data['reason'] ?? 'inventory_count',
                'user_id'           => auth()->id(),
                'notes'             => $data['notes'] ?? "System Qty: {$systemQuantity}, Actual: {$actualQuantity}",
            ]);
        });
    }
}