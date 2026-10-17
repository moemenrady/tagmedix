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

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function getPendingAdvancesAmount(): float
    {
        return (float) $this->advances()
            ->where('is_settled', false)
            ->where('type', 'advance')
            ->sum('amount');
    }

    public function calculatePieceEarningsForPeriod(int $month, int $year): array
    {
        // Get logs for this employee in the month
        $logs = $this->productionStepLogs()
            ->whereMonth('executed_at', $month)
            ->whereYear('executed_at', $year)
            ->with('order')
            ->get();

        $piecePrices = TailorPiecePrice::where('employee_id', $this->id)
            ->get()
            ->keyBy('order_id');

        $earnings = 0;
        $orderIdsSeen = [];

        foreach ($logs as $log) {
            if (!in_array($log->order_id, $orderIdsSeen)) {
                $orderIdsSeen[] = $log->order_id;
                if (isset($piecePrices[$log->order_id])) {
                    $earnings += (float) $piecePrices[$log->order_id]->piece_price;
                }
            }
        }

        return [
            'earnings' => $earnings,
            'pieces_count' => count($orderIdsSeen),
        ];
    }
}
