<?php

namespace App\Models;

use App\Integrations\Turbo\Models\Shipment;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model

{

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_price',
        'shipping_due_amount',
        'return_due_amount',
        'order_contents',
        'source',
        'status',
    ];


    public function getTotalPaid()
    {
        return $this->payments_sum_amount ?? 0;
    }

    public function getRemainingAmount()
    {
        return $this->total_price - $this->getTotalPaid();
    }
    public function getCustomerNameAttribute()
    {
        return $this->customer?->name ?? '';
    }

    public function getPhone1Attribute()
    {
        return $this->customer?->phone_1 ?? '';
    }

    public function getPhone2Attribute()
    {
        return $this->customer?->phone_2 ?? '';
    }

    public function getGovernorateAttribute()
    {
        return $this->address?->governorate ?? '';
    }

    public function getFullAddressAttribute()
    {
        return $this->address?->full_address ?? '';
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            $latestOrder = static::orderBy('id', 'desc')->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;

            $order->order_number =
                'ORD-' . str_pad(
                    $nextId,
                    5,
                    '0',
                    STR_PAD_LEFT
                );
        });
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->hasOne(CustomerOrderAddress::class);
    }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function productionTasks()
    {
        return $this->hasMany(ProductionTask::class);
    }
    public function productionLogs()
    {
        return $this->hasMany(ProductionStepLog::class);
    }
    public function histories()
    {
        return $this->hasMany(ProductionHistory::class);
    }
    public function tailorPiecePrices()
    {
        return $this->hasMany(TailorPiecePrice::class);
    }
    public function assignments()
    {
        return $this->hasMany(ProductionAssignment::class);
    }
}
