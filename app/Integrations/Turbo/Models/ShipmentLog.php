<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentLog extends Model
{
    protected $table = 'shipment_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
