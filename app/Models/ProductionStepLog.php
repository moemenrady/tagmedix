<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionStepLog extends Model
{
    protected $fillable = [

        'order_id',

        'from_status',

        'to_status',

        'changed_by',

        'employee_id',

        'note',

        'executed_at',
    ];

    protected $casts = [

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

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
