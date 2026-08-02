<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorPiecePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'order_id',
        'piece_price',
    ];

    protected $casts = [
        'piece_price' => 'decimal:2',
    ];

    /**
     * الخياط.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    /**
     * الأوردر.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
