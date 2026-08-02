<?php

use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Foundation\Testing\TestCase;

uses(TestCase::class);

it('can be instantiated without a client abstraction', function (): void {
    $service = new TurboService();

    expect($service)->toBeInstanceOf(TurboService::class);
});
