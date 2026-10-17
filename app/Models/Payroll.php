<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_number',
        'employee_id',
        'period_month',
        'period_year',
        'salary_type',
        'base_salary',
        'piece_rate_earnings',
        'completed_pieces_count',
        'bonuses_amount',
        'deductions_amount',
        'advances_deducted',
        'net_salary',
        'status',
        'paid_at',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'base_salary' => 'decimal:2',
        'piece_rate_earnings' => 'decimal:2',
        'completed_pieces_count' => 'integer',
        'bonuses_amount' => 'decimal:2',
        'deductions_amount' => 'decimal:2',
        'advances_deducted' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public static function generatePayrollNumber(): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';
        $last = self::where('payroll_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('payroll_number');

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateNet(): void
    {
        $gross = ($this->salary_type === 'monthly' ? $this->base_salary : $this->piece_rate_earnings) + $this->bonuses_amount;
        $deductions = $this->deductions_amount + $this->advances_deducted;
        $this->net_salary = max(0, $gross - $deductions);
    }
}
