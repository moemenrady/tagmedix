<?php

namespace App\Models;

use App\Integrations\Turbo\Models\Shipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'order_number',
        'customer_name',
        'customer_phone_1',
        'customer_phone_2',
        'customer_instagram',
        'total_price',
        'shipping_due_amount',
        'return_due_amount',
        'order_contents',
        'source',
        'status',
    ];

    // إرجاع قيمة Snapshot إن وجدت، أو العودة لبيانات العميل الحالية
    public function getCustomerNameAttribute()
    {
        return $this->attributes['customer_name'] ?? $this->customer?->name ?? '';
    }

    public function getPhone1Attribute()
    {
        return $this->attributes['customer_phone_1'] ?? $this->customer?->phone_1 ?? '';
    }

    public function getPhone2Attribute()
    {
        return $this->attributes['customer_phone_2'] ?? $this->customer?->phone_2 ?? '';
    }

    public function getGovernorateAttribute()
    {
        return $this->address?->governorate ?? '';
    }

    public function getFullAddressAttribute()
    {
        return $this->address?->full_address ?? '';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->hasOne(CustomerOrderAddress::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaid()
    {
        return $this->payments_sum_amount ?? 0;
    }

    public function getRemainingAmount()
    {
        return $this->total_price - $this->getTotalPaid();
    }



    public function shipments()
    {
        return $this->hasMany(Shipment::class);
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
