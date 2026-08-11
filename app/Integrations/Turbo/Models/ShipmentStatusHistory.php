<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentStatusHistory extends Model
{
    protected $table = 'shipment_status_histories';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'response' => 'array',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
