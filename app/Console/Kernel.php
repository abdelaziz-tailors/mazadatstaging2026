<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        Commands\CheckLiveVideoStatus::class,
        Commands\CheckLiveVideoEnd::class,
        Commands\CheckRecordedVideoStart::class,
        Commands\SendUpcomingAuctionReminders::class,
        Commands\SendSubscriptionExpiryReminders::class,
    ];

    protected function schedule(Schedule $schedule)
    {

        // $schedule->command('inspire')->hourly();
        $schedule->command('livevideo:check')->everyMinute();
        $schedule->command('livevideoEnd:check')->everyMinute();
        $schedule->command('recordedvideostart:check')->everyMinute();
        $schedule->command('notifications:upcoming-auction-reminders')->everyFiveMinutes();
        $schedule->command('notifications:subscription-expiry-reminders')->hourly();
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
