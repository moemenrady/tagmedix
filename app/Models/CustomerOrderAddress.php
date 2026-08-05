<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'country',
        'governorate',
        'city',
        'full_address',
    ];

    protected $appends = [
        'turbo_government',
        'turbo_area',
    ];

    public function getTurboGovernmentAttribute(): string
    {
        if ($this->governorate === 'شحن دولي') {
            $destination = $this->country ?: $this->city;

            return $destination !== '' ? "{$destination}-شحن دولي" : 'شحن دولي';
        }

        $parts = array_values(array_filter([
            $this->country ?: 'مصر',
            $this->governorate,
        ]));

        if ($this->city && $this->city !== $this->governorate) {
            $parts[] = $this->city;
        }

        return implode('-', $parts);
    }

    public function getTurboAreaAttribute(): string
    {
        if ($this->governorate === 'شحن دولي') {
            return '';
        }

        if ($this->city && $this->city !== $this->governorate) {
            return $this->city;
        }

        return '';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}