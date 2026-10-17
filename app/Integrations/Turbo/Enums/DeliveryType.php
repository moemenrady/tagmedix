<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Enums;

enum DeliveryType: string
{
    case STANDARD = 'standard';
    case EXPRESS = 'express';
    case SCHEDULED = 'scheduled';
}
