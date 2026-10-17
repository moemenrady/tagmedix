<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemStander extends Model
{
    protected $table = 'order_item_stander';

    protected $fillable = [
        'pant_type_id',
        'type_id',
        'style_id',
        'color_id',
        'size_id',
        'sleeve_type_id',
        'price',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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