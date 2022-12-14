<?php

namespace App\Console;

use App\Console\Commands\UpdateInventory;
use App\Console\Commands\UpdatePricing;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\UpdateInventory',
        'App\Console\Commands\UpdatePricing',
        'App\Console\Commands\ReviseAllItems',
        'App\Console\Commands\FlushRedis',
        'App\Console\Commands\UpdateDatabase',
        'App\Console\Commands\UpdateAllPrices',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(UpdateInventory::class, ['supplier' => 'pf'])->dailyAt('07:46');
        $schedule->command(UpdateInventory::class, ['supplier' => 'lkq'])->dailyAt('08:00');
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
