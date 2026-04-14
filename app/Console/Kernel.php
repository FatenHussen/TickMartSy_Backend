<?php

namespace App\Console;

use App\Jobs\SendScheduledBasketReminderJob;
use App\Jobs\NotifyExpiredProductsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // تشغيل job انتهاء صلاحية النقاط يومياً في منتصف الليل
        $schedule->job(\App\Jobs\ExpirePointsJob::class)->daily();
        $schedule->job(NotifyExpiredProductsJob::class)->dailyAt('08:00');
        $schedule->job(new SendScheduledBasketReminderJob)->dailyAt('09:00');
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
