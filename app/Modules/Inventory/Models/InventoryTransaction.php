<?php

namespace App\Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'inventory_item_id',
        'inventory_item_variant_id',
        'inventory_unit_id',
        'type',
        'quantity',
        'unit_type',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'transaction_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Main inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    /**
     * Item variant / color.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItemVariant::class,
            'inventory_item_variant_id'
        );
    }

    /**
     * Physical inventory unit.
     */
    public function inventoryUnit(): BelongsTo
    {
        return $this->belongsTo(
            InventoryUnit::class,
            'inventory_unit_id'
        );
    }



    /**
     * User who created the transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Polymorphic business reference.
     *
     * Examples:
     * production_order
     * order
     * purchase
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}