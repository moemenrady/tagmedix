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

    protected $appends = [
        'captain_name',
        'captain_phone',
        'captain_whatsapp_url',
        'delay_reason',
        'return_reason',
        'current_branch',
        'expected_branch',
        'is_delayed',
        'is_delivered',
        'is_returned',
        'is_canceled',
        'is_in_transit',
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

    public function getCaptainNameAttribute(): ?string
    {
        return $this->attributes['captain_name']
            ?? $this->response['captain_name']
            ?? $this->response['captain']
            ?? null;
    }

    public function getCaptainPhoneAttribute(): ?string
    {
        $phone = $this->attributes['captain_phone']
            ?? $this->response['captain_number1']
            ?? $this->response['captain_phone']
            ?? $this->response['captain_number']
            ?? null;

        return $phone ? trim((string) $phone) : null;
    }

    public function getCaptainWhatsappUrlAttribute(): ?string
    {
        $phone = $this->captain_phone;
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            $digits = '20' . substr($digits, 1);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '20' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }

    public function getDelayReasonAttribute(): ?string
    {
        return $this->attributes['delay_reason']
            ?? $this->response['delay_reason']
            ?? null;
    }

    public function getReturnReasonAttribute(): ?string
    {
        return $this->attributes['return_reason']
            ?? $this->response['return_reason']
            ?? null;
    }

    public function getCurrentBranchAttribute(): ?string
    {
        return $this->response['branch']
            ?? $this->response['branch_name']
            ?? null;
    }

    public function getExpectedBranchAttribute(): ?string
    {
        return $this->response['expected_branch'] ?? null;
    }

    public function getIsDelayedAttribute(): bool
    {
        return ! empty($this->delay_reason) || str_contains(strtolower((string) $this->status), 'delay');
    }

    public function getIsDeliveredAttribute(): bool
    {
        $s = strtolower((string) $this->status);

        return in_array($s, ['delivered', 'completed', '3', 'تم التسليم', 'مكتمل']);
    }

    public function getIsReturnedAttribute(): bool
    {
        $s = strtolower((string) $this->status);

        return in_array($s, ['returned', 'canceled', 'cancelled', '4', 'مرتجع', 'ملغي', 'محذوفة', 'محذوف', 'deleted'])
            || str_contains($s, 'مرتجع')
            || str_contains($s, 'ملغي')
            || str_contains($s, 'محذوف');
    }

    public function getIsCanceledAttribute(): bool
    {
        $s = strtolower((string) $this->status);

        return in_array($s, ['canceled', 'cancelled', '4', 'ملغي', 'محذوفة', 'محذوف', 'deleted'])
            || str_contains($s, 'ملغي')
            || str_contains($s, 'محذوف');
    }

    public function getIsInTransitAttribute(): bool
    {
        $s = strtolower((string) $this->status);

        return in_array($s, ['in_transit', 'delivering', 'shipped', '2', 'جاري التوصيل', 'مع المندوب']);
    }
}