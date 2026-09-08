<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryFinishedProduct extends Model
{
    protected $table = 'inventory_finished_products';

    protected $fillable = [
        'inventory_item_id',
        'inventory_item_variant_id',
        'product_code',

        'pant_type_id',
        'type_id',
        'style_id',
        'color_id',
        'size_id',
        'sleeve_type_id',

        'quantity',
        'unit_cost',
        'total_cost',

        'notes',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItemVariant::class,
            'inventory_item_variant_id'
        );
    }

    public function pantType(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\PantType::class,
            'pant_type_id'
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Type::class,
            'type_id'
        );
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Style::class,
            'style_id'
        );
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Color::class,
            'color_id'
        );
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Size::class,
            'size_id'
        );
    }

    public function sleeveType(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\SleeveType::class,
            'sleeve_type_id'
        );
    }
}