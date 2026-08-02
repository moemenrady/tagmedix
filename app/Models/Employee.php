<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'hire_date',
        'salary_type',
        'monthly_salary',
        'active',
    ];
    protected $casts = [
        'hire_date' => 'date',
        'monthly_salary' => 'decimal:2',
        'active' => 'boolean',
    ];
    public function productionAssignments()
    {
        return $this->hasMany(ProductionAssignment::class);
    }

    public function productionStepLogs()
    {
        return $this->hasMany(ProductionStepLog::class);
    }

    public function tailorPiecePrices()
    {
        return $this->hasMany(TailorPiecePrice::class);
    }
    public function piecePrices()
    {
        return $this->hasMany(TailorPiecePrice::class);
    }
}
