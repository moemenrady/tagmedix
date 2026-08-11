<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Events;

use App\Integrations\Turbo\Models\Shipment;
use Illuminate\Foundation\Events\Dispatchable;

final class ShipmentCreated
{
    use Dispatchable;

    public function __construct(public readonly Shipment $shipment)
    {
    }
}
