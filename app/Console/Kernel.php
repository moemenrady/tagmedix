<?php

namespace App\Console;

use App\Integrations\Turbo\Jobs\SyncTurboStatusJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new SyncTurboStatusJob())->hourly();
    }
}
