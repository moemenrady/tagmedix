<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'type', // تم إضافة النوع
        'amount',
        'payment_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // دوال مساعدة لمعرفة نوع الدفعة بسهولة في واجهة المستخدم
    public function isOrderAdvance(): bool
    {
        return $this->type === 'order_advance';
    }

    public function isCompanySupply(): bool
    {
        return $this->type === 'company_supply';
    }
}