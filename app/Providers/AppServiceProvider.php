<?php

namespace App\Providers;

use App\Models\SetionNotAdded;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
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
        $modulesPath = app_path('Modules');

        if (is_dir($modulesPath)) {
            foreach (File::directories($modulesPath) as $module) {
                $migrationPath = $module . '/Database/Migrations';

                if (is_dir($migrationPath)) {
                    $this->loadMigrationsFrom($migrationPath);
                }
            }
        }

        $this->loadMigrationsFrom(database_path('migrations/turbo'));
    }
}
