<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
  protected $fillable = ['name', 'identifier'];


  public function orderItems()
  {
    return $this->hasMany(OrderItem::class);
  }
  protected static function booted()
  {
    static::creating(function ($order) {
      if (! $order->order_number) {
        $latestId = static::max('id') ?? 0;
        $nextId = $latestId + 1;

        $order->order_number = 'ORD-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
      }
    });
  }
}
