<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone_1',
        'phone_2',
        'instagram_profile',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderAddresses(): HasMany
    {
        return $this->hasMany(CustomerOrderAddress::class);
    }
}