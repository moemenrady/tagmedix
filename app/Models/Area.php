<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'government_id',
        'name',
        'real_id',
    ];

    protected $casts = [
        'real_id' => 'integer',
        'government_id' => 'integer',
    ];

    public function government()
    {
        return $this->belongsTo(Government::class);
    }
}