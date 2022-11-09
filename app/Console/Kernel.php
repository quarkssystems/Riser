<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('masterclass:payout')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('masterclassaffiliate:payout')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('masterclass:missed')->daily()->timezone('Asia/Kolkata');

        $schedule->command('callbooking:payout')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('callbooking:missed')->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('notification:master-class-meeting')->everyMinute()->withoutOverlapping();
        $schedule->command('notification:call-booking')->everyMinute()->withoutOverlapping();
        $schedule->command('bunny:update-status')->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('post:likes')->dailyAt('23:00')->timezone('Asia/Kolkata');
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
