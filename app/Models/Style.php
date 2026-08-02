<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Style extends Model
{
    protected $fillable = ['name', 'identifier'];


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
