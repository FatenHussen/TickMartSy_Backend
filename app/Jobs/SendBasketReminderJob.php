<?php

namespace App\Jobs;

use App\Models\UserBasketSchedule;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\Base\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScheduledBasketReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $userSchedules = UserBasketSchedule::with('user')
            ->whereDate('next_run_date', $tomorrow)
            ->whereNull('paused_at')
            ->get();

        foreach ($userSchedules as $schedule) {
            $notificationService->send(
                $schedule->user,
                'Basket Reminder',
                'Your scheduled basket will be ordered tomorrow.',
                [
                    'type' => 'basket_reminder',
                    'basket_id' => $schedule->id,
                ]
            );
        }

        $orders = Order::with('user')
            ->whereDate('next_run_date', $tomorrow)
            ->whereNull('paused_at')
            ->get();

        foreach ($orders as $order) {
            $notificationService->send(
                $order->user,
                'Basket Reminder',
                'Your subscription basket will be processed tomorrow.',
                [
                    'type' => 'subscription_reminder',
                    'order_id' => $order->id,
                ]
            );
        }
    }
}
