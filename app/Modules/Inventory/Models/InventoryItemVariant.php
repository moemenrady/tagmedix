<?php

namespace App\Modules\Inventory\Models;

use App\Models\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItemVariant extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_item_variants';

    protected $fillable = [
        'inventory_item_id',
        'color_id',
        'sku',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(
            Color::class,
            'color_id'
        );
    }

    public function inventoryUnits(): HasMany
    {
        return $this->hasMany(
            InventoryUnit::class,
            'inventory_item_variant_id'
        );
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(
            InventoryStock::class,
            'inventory_item_variant_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            InventoryTransaction::class,
            'inventory_item_variant_id'
        );
    }
}
