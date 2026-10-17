<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class InventoryUnit extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_units';

    protected $fillable = [
        'inventory_item_variant_id',
        'unit_code',
        'quantity',
        'unit_type',
        'status',
        'unit_cost',
        'total_cost',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'received_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItemVariant::class,
            'inventory_item_variant_id'
        );
    }



    public function transactions(): HasMany
{
    return $this->hasMany(
        InventoryTransaction::class,
        'inventory_unit_id'
    );
}
}
