<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\User;
use App\Models\ResetPassword;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reservations:update-status')->everyMinute();
        $schedule->call(function(){
            User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subHour())
            ->delete();
        })->everyMinute();
        $schedule->call(function(){
            ResetPassword::where('created_at', '<', now()->subHour())
            ->delete();
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
