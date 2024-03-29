<?php

namespace App\Console;

use App\Jobs\CompletionShipments;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Bus;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GrabWialonObjects::class,
        \App\Console\Commands\GrabWialonResources::class,
        \App\Console\Commands\ClearWialon::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            CompletionShipments::dispatch();
        })->dailyAt('00:00');

        $schedule->command('grab:wialon-objects')->dailyAt('02:00');
        $schedule->command('grab:wialon-resources')->dailyAt('02:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');

    }
}
