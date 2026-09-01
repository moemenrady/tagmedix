<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventoryTransactionService
{
    /**
     * تسجيل حركة مخزون جديدة
     * 
     * @param array $data
     * @return InventoryTransaction
     * @throws Exception
     */
    public function recordTransaction(array $data): InventoryTransaction
    {
        // التحقق من وجود البيانات الأساسية المطلوبة
        $this->validateRequiredData($data);

        // حساب التكلفة الإجمالية للحركة إذا لم يتم تمريرها
        $totalCost = $data['total_cost'] ?? ($data['quantity'] * ($data['cost_per_unit'] ?? 0));

        // إنشاء الحركة في قاعدة البيانات
        return InventoryTransaction::create([
            'inventory_item_id' => $data['inventory_item_id'],
            'inventory_unit_id' => $data['inventory_unit_id'] ?? null,
            'type'              => $data['type'], // stock_in, stock_out, adjustment, return, transfer
            'quantity'          => $data['quantity'],
            'unit'              => $data['unit'],
            'cost_per_unit'     => $data['cost_per_unit'] ?? 0,
            'total_cost'        => $totalCost,
            'reason'            => $data['reason'] ?? null,
            'reference_type'    => $data['reference_type'] ?? null,
            'reference_id'      => $data['reference_id'] ?? null,
            'user_id'           => $data['user_id'] ?? Auth::id(),
            'notes'             => $data['notes'] ?? null,
        ]);
    }

    /**
     * التحقق من البيانات الأساسية قبل التسجيل
     */
    protected function validateRequiredData(array $data): void
    {
        if (empty($data['inventory_item_id'])) {
            throw new Exception("يجب تحديد الصنف (Item ID) لتسجيل الحركة.");
        }

        if (empty($data['type'])) {
            throw new Exception("يجب تحديد نوع الحركة (Transaction Type).");
        }

        if (!isset($data['quantity']) || $data['quantity'] == 0) {
            // نسمح بالقيم السالبة والموجبة (لـ Adjustment) لكن لا نسمح بالصفر
            throw new Exception("كمية الحركة لا يمكن أن تكون صفرًا.");
        }

        if (empty($data['unit'])) {
            throw new Exception("يجب تحديد وحدة القياس (Unit).");
        }
    }

    /**
     * جلب سجل الحركات لصنف معين (أو وحدة معينة)
     */
    public function getTransactionHistory($itemId, $unitId = null)
    {
        $query = InventoryTransaction::with(['user', 'item', 'unit', 'reference'])
                    ->where('inventory_item_id', $itemId);

        if ($unitId) {
            $query->where('inventory_unit_id', $unitId);
        }

        // إرجاع الحركات مرتبة من الأحدث للأقدم
        return $query->orderBy('created_at', 'desc')->get();
    }
}