<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionHistory extends Model
{
    protected $fillable = [

        'order_id',

        'user_id',

        'action',

        'old_value',

        'new_value',

        'ip',

        'device',

        'executed_at',
    ];

    protected $casts = [

        'old_value' => 'array',

        'new_value' => 'array',

        'executed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
