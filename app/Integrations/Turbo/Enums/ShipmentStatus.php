<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Enums;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
