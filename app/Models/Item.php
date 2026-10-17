<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model

{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'unit',
        'description',
        'minimum_quantity',
        'current_quantity',
        'last_purchase_price',
        'is_active',
    ];

    protected $casts = [
        'minimum_quantity' => 'decimal:2',
        'current_quantity' => 'decimal:2',
        'last_purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * التصنيف
     */
    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    /**
     * إجمالي قيمة المخزون
     */
    public function getTotalValueAttribute()
    {
        return $this->current_quantity * $this->last_purchase_price;
    }

    /**
     * هل الكمية وصلت للحد الأدنى؟
     */
    public function getIsLowStockAttribute()
    {
        return $this->current_quantity <= $this->minimum_quantity;
    }
}
