<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        $schedule->command('plan:cron');
            // ->daily();
        $schedule->command('course-finished-earn-coin');
            // ->everyFiveMinute(); 
        $schedule->command('video-finished-earn-coin');
            // ->everyTenMinute();
        $schedule->command('delete-report-file');
        $schedule->command('app:renewing-subscriptions');
        $schedule->command('app:get-failed-and-authorized');
        //->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
