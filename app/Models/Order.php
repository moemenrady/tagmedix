<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    protected static function booted()
    {
        static::creating(function ($order) {

            $order->order_number =
                'ORD-' . str_pad(
                    static::count() + 1,
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

    public function items()
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
