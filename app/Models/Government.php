<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Government.php
class Government extends Model {
    public $incrementing = false;
    protected $fillable = ['id', 'name'];
    
    public function areas() {
        return $this->hasMany(Area::class);
    }
}
