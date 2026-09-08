<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


// app/Models/Area.php
class Area extends Model {
    public $incrementing = false;
    protected $fillable = ['id', 'government_id', 'name'];
    
    public function government() {
        return $this->belongsTo(Government::class);
    }
}