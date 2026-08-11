<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $table = 'shipments';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'amount' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_order' => 'boolean',
        'is_fragile' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ShipmentLog::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class);
    }
}
