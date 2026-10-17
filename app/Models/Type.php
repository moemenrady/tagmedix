<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = ['name', 'identifier', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

     public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
