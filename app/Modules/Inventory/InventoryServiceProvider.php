<?php

namespace App\Modules\Inventory;

use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }
}
