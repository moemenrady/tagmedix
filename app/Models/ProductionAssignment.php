<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionAssignment extends Model
{
    protected $fillable = [

        'order_id',

        'stage',

        'employee_id',

        'assigned_by',

        'assigned_at',
    ];

    protected $casts = [

        'assigned_at' => 'datetime',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
