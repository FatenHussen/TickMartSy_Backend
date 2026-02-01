<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Admin;
use App\Models\Driver;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderNotifications implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $this->notifyAdmins($order);

        if ($order->is_instant_delivery) {
            $this->notifyDrivers($order);
        }
    }

    protected function notifyAdmins($order): void
    {
        $title = $order->is_instant_delivery
            ? 'طلب فوري جديد'
            : 'طلب توصيل جديد';

        $body = $order->is_instant_delivery
            ? 'تم إنشاء طلب توصيل فوري'
            : 'تم إنشاء طلب توصيل غير فوري';

        Admin::query()->each(
            fn($admin) =>
            NotificationService::sendAppointmentNotification(
                $admin,
                $title,
                $body,
                [
                    'order_id' => $order->id,
                    'type' => $order->is_instant_delivery ? 'instant' : 'scheduled'
                ]
            )
        );
    }

    protected function notifyDrivers($order): void
    {
        Driver::where('is_active', true)
            ->each(
                fn($driver) =>
                NotificationService::sendAppointmentNotification(
                    $driver,
                    'طلب توصيل فوري',
                    'يوجد طلب فوري جديد بحاجة لتوصيل',
                    [
                        'order_id' => $order->id,
                        'type' => 'instant'
                    ]
                )
            );
    }
}
