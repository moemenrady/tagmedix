<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'unit_cost',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * الصنف
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * المستخدم الذي نفذ الحركة
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * تكلفة الحركة
     */
    public function getTotalCostAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * هل الحركة إضافة؟
     */
    public function isIncoming(): bool
    {
        return in_array($this->type, [
            'purchase',
            'adjustment_in',
            'return_in',
        ]);
    }

    /**
     * هل الحركة صرف؟
     */
    public function isOutgoing(): bool
    {
        return in_array($this->type, [
            'issue',
            'adjustment_out',
            'return_out',
        ]);
    }
}
