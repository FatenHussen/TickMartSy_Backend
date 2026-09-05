<?php

namespace App\Jobs;

use App\Enums\CartType;
use App\Models\UserBasketSchedule;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\Base\NotificationService;
use App\Services\ScheduledBasketAlertService;
use App\Services\ScheduledBasketAvailabilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScheduledBasketReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        NotificationService $notificationService,
        ScheduledBasketAvailabilityService $availabilityService,
        ScheduledBasketAlertService $alertService,
    ): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $userSchedules = UserBasketSchedule::with(['user', 'schedule', 'items.product', 'items.variant.productVariant.product'])
            ->where('is_active', true)
            ->where('is_draft', false)
            ->whereNull('paused_at')
            ->whereNotNull('start_date')
            ->whereHas('schedule', fn($query) => $query->where('is_active', true))
            ->get();

        foreach ($userSchedules as $schedule) {
            $schedule->next_run_date = $this->resolveUserScheduleNextRunDate($schedule);
            $summary = $availabilityService->evaluateUserSchedule($schedule);
            $syncResult = $alertService->syncForUserSchedule($schedule, $summary);
            $resolvedNotificationSent = false;

            if ($syncResult['should_notify_resolved'] && $syncResult['resolved_alert']) {
                $notificationService->send(
                    $schedule->user,
                    'رجعت السلة كاملة',
                    "كل عناصر السلة المجدولة {$schedule->name} متوفرة الآن.",
                    [
                        'type' => 'scheduled_basket_back_in_stock',
                        'target_screen' => 'scheduled_basket_details',
                        'scheduled_basket_id' => $schedule->id,
                        'alert_id' => $syncResult['resolved_alert']->id,
                        'next_run_date' => $summary['next_run_date'],
                    ]
                );

                $alertService->markResolutionNotificationSent($syncResult['resolved_alert']);
                $resolvedNotificationSent = true;
            }

            if ($summary['next_run_date'] !== $tomorrow) {
                continue;
            }

            if ($syncResult['active_alert']) {
                if ($syncResult['should_notify_issue']) {
                    $notificationService->send(
                        $schedule->user,
                        'في عناصر ناقصة بالسلة المجدولة',
                        "بعض عناصر السلة {$schedule->name} غير متوفرة أو الكمية لا تكفي حاليًا.",
                        [
                            'type' => 'scheduled_basket_stock_issue',
                            'target_screen' => 'scheduled_basket_details',
                            'scheduled_basket_id' => $schedule->id,
                            'alert_id' => $syncResult['active_alert']->id,
                            'next_run_date' => $summary['next_run_date'],
                        ]
                    );

                    $alertService->markIssueNotificationSent($syncResult['active_alert']);
                }

                continue;
            }

            if ($resolvedNotificationSent) {
                continue;
            }

            $notificationService->send(
                $schedule->user,
                'تذكير بالسلة المجدولة',
                "غداً موعد السلة المجدولة {$schedule->name}.",
                [
                    'type' => 'basket_reminder',
                    'target_screen' => 'scheduled_basket_details',
                    'scheduled_basket_id' => $schedule->id,
                    'next_run_date' => $summary['next_run_date'],
                ]
            );
        }

        $orders = Order::with([
            'user',
            'basket.items.product',
            'basket.items.shopProductVariant.productVariant.product',
            'basketSchedule',
        ])
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->whereNotNull('basket_id')
            ->whereNotNull('basket_schedule_id')
            ->whereNull('pause_at')
            ->whereHas('basket', fn($query) => $query->where('is_active', true))
            ->whereHas('basketSchedule', fn($query) => $query->where('is_active', true))
            ->get()
            ->sortByDesc('created_at')
            ->unique(fn(Order $order) => "{$order->user_id}-{$order->basket_id}");

        foreach ($orders as $order) {
            if (!$order->basket || !$order->basketSchedule) {
                continue;
            }

            $summary = $availabilityService->evaluateAdminSchedule($order->basket, $order);
            $syncResult = $alertService->syncForAdminSchedule($order, $order->basket, $order->basketSchedule, $summary);
            $resolvedNotificationSent = false;

            if ($syncResult['should_notify_resolved'] && $syncResult['resolved_alert']) {
                $notificationService->send(
                    $order->user,
                    'رجعت السلة كاملة',
                    'كل عناصر السلة المجدولة من سلاتي متوفرة الآن.',
                    [
                        'type' => 'scheduled_basket_back_in_stock',
                        'target_screen' => 'my_baskets',
                        'basket_id' => $order->basket_id,
                        'basket_schedule_id' => $order->basket_schedule_id,
                        'order_id' => $order->id,
                        'alert_id' => $syncResult['resolved_alert']->id,
                        'next_run_date' => $summary['next_run_date'],
                    ]
                );

                $alertService->markResolutionNotificationSent($syncResult['resolved_alert']);
                $resolvedNotificationSent = true;
            }

            if ($summary['next_run_date'] !== $tomorrow) {
                continue;
            }

            if ($syncResult['active_alert']) {
                if ($syncResult['should_notify_issue']) {
                    $notificationService->send(
                        $order->user,
                        'في عناصر ناقصة بالسلة المجدولة',
                        'بعض عناصر السلة المجدولة من سلاتي غير متوفرة أو الكمية لا تكفي حاليًا.',
                        [
                            'type' => 'scheduled_basket_stock_issue',
                            'target_screen' => 'my_baskets',
                            'basket_id' => $order->basket_id,
                            'basket_schedule_id' => $order->basket_schedule_id,
                            'order_id' => $order->id,
                            'alert_id' => $syncResult['active_alert']->id,
                            'next_run_date' => $summary['next_run_date'],
                        ]
                    );

                    $alertService->markIssueNotificationSent($syncResult['active_alert']);
                }

                continue;
            }

            if ($resolvedNotificationSent) {
                continue;
            }

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
                    'next_run_date' => $summary['next_run_date'],
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
