<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Enums;

enum InternalStatus: string
{
    case CREATED = 'created';
    case SUBMITTED = 'submitted';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
