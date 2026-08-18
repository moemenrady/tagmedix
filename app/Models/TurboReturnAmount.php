<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurboReturnAmount extends Model
{
    protected $fillable = [
        'government_name',
        'area_name',
        'return_amount',
    ];
}
