<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'order_id',
        'customer_id',
        'return_type', // full, partial
        'reason',
        'status', // pending, received, inspected, completed, rejected
        'qc_status', // pending, good, damaged, partial_good
        'refund_amount',
        'shipping_cost_deducted',
        'refund_status', // pending, refunded, no_refund
        'action_taken',
        'notes',
        'created_by',
        'received_at',
        'completed_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'shipping_cost_deducted' => 'decimal:2',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET-' . date('Ym') . '-';
        $last = self::where('return_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('return_number');

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
