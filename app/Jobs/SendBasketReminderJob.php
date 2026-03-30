<?php

namespace App\Jobs;

use App\Enums\CartType;
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

        $userSchedules = UserBasketSchedule::with(['user', 'schedule'])
            ->where('is_active', true)
            ->whereNull('paused_at')
            ->whereNotNull('start_date')
            ->whereHas('schedule', fn($query) => $query->where('is_active', true))
            ->get()
            ->filter(fn(UserBasketSchedule $schedule) => $this->resolveUserScheduleNextRunDate($schedule)?->toDateString() === $tomorrow);

        foreach ($userSchedules as $schedule) {
            $nextRunDate = $this->resolveUserScheduleNextRunDate($schedule)?->toDateString();

            $notificationService->send(
                $schedule->user,
                'تذكير بالسلة المجدولة',
                "غداً موعد السلة المجدولة {$schedule->name}.",
                [
                    'type' => 'basket_reminder',
                    'target_screen' => 'scheduled_basket_details',
                    'scheduled_basket_id' => $schedule->id,
                    'next_run_date' => $nextRunDate,
                ]
            );
        }

        $orders = Order::with(['user', 'basket', 'basketSchedule'])
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->whereNotNull('basket_id')
            ->whereNotNull('basket_schedule_id')
            ->whereNull('pause_at')
            ->whereHas('basketSchedule', fn($query) => $query->where('is_active', true))
            ->get()
            ->sortByDesc('created_at')
            ->unique(fn(Order $order) => "{$order->user_id}-{$order->basket_id}-{$order->basket_schedule_id}")
            ->filter(fn(Order $order) => $order->next_run_date?->toDateString() === $tomorrow);

        foreach ($orders as $order) {
            $nextRunDate = $order->next_run_date?->toDateString();

            $notificationService->send(
                $order->user,
                'تذكير بالسلة المجدولة',
                'غداً موعد السلة المجدولة من سلاتي.',
                [
                    'type' => 'subscription_reminder',
                    'target_screen' => 'my_baskets',
                    'basket_id' => $order->basket_id,
                    'basket_schedule_id' => $order->basket_schedule_id,
                    'order_id' => $order->id,
                    'next_run_date' => $nextRunDate,
                ]
            );
        }
    }

    private function resolveUserScheduleNextRunDate(UserBasketSchedule $schedule): ?Carbon
    {
        if (!$schedule->start_date || !$schedule->schedule) {
            return null;
        }

        return $this->resolveNextRunDate(
            Carbon::parse($schedule->start_date)->startOfDay(),
            (int) $schedule->schedule->interval_days,
        );
    }

    private function resolveNextRunDate(Carbon $startDate, int $intervalDays): ?Carbon
    {
        if ($intervalDays <= 0) {
            return null;
        }

        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return $startDate->copy();
        }

        $daysPassed = $startDate->diffInDays($today);
        $cycles = intdiv($daysPassed, $intervalDays) + 1;

        return $startDate->copy()->addDays($cycles * $intervalDays);
    }
}
