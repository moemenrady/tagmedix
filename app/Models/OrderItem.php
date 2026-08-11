<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'style_id',
        'color_id',
        'size_id',
        'type_id',
        'pant_type_id',
        'sleeve',
        'quantity',
        'price',
        'total',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function style()
    {
        return $this->belongsTo(Style::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
       public function pantType()
    {
        return $this->belongsTo(PantType::class);
    }
}
