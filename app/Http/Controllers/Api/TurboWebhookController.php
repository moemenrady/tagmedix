<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Integrations\Turbo\Webhooks\TurboWebhookController as TurboWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TurboWebhookController extends Controller
{
    public function __construct(private readonly TurboWebhookHandler $handler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->handler->handle($request);
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->handler->handle($request);
    }
}
