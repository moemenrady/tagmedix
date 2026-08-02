<?php

declare(strict_types=1);

namespace App\Providers;

use App\Integrations\Turbo\Repositories\Eloquent\ShipmentRepository;
use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Support\ServiceProvider;

final class TurboServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TurboService::class);
        $this->app->bind(ShipmentRepositoryInterface::class, ShipmentRepository::class);
    }

    public function boot(): void
    {
    }
}
