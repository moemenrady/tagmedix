<?php

namespace App\Integrations\Turbo\Models;

use App\Integrations\Turbo\Models\ShipmentLog;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Shipment extends Model
{
    protected $fillable = [
        'order_id',

        'turbo_id',
        'remote_order_id',
        'remote_shipment_id',

        'tracking_number',

        'receiver',
        'phone',
        'phone2',

        'government',
        'area',
        'address',

        'amount',
        'collected_amount',
        'weight',

        'notes',

        'order_summary',
        'return_summary',
        'return_amount',

        'status',
        'status_text',
        'internal_status',

        'delivery_type',

        'is_order',
        'is_fragile',

        'invoice_number',
        'api_followup_phone',

        'number_of_items',
        'can_open',

        'shipped_at',
        'delivered_at',
        'returned_at',
        'last_synced_at',

        'payload',
        'response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
        'weight' => 'decimal:2',

        'is_order' => 'boolean',
        'is_fragile' => 'boolean',
        'can_open' => 'boolean',

        'payload' => 'array',
        'response' => 'array',

        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ShipmentLog::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class);
    }
}