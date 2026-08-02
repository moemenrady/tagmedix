<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTask extends Model
{
    protected $fillable = [

        'order_id',

        'assigned_to',

        'assigned_by',

        'title',

        'instruction',

        'required_date',

        'priority',

        'status',

        'completed_at',
    ];

    protected $casts = [

        'required_date' => 'date',

        'completed_at' => 'datetime',
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}