<?php

namespace App\Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit_type',
        'stock_tracking_type',
        'is_active',
        'minimum_stock',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minimum_stock' => 'decimal:3',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function variants(): HasMany
    {
        return $this->hasMany(
            InventoryItemVariant::class,
            'inventory_item_id'
        );
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(
            InventoryStock::class,
            'inventory_item_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            InventoryTransaction::class,
            'inventory_item_id'
        );
    }
}