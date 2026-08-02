<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{

    protected $fillable = [
        'order_number',
        'customer_name',
        'phone_1',
        'phone_2',
        'governorate',
        'full_address',
        'instagram_profile',
        'total_price',
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
