<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WebhookReceived
{
    use Dispatchable;

    public function __construct(public readonly array $payload)
    {
    }
}
