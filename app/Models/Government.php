<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Government.php
class Government extends Model
{
    protected $fillable = [
        'name',
        'real_id',
    ];

    protected $casts = [
        'real_id' => 'integer',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}