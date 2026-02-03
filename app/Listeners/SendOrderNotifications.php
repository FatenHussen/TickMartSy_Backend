<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Admin;
use App\Models\Driver;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(OrderCreated $event): void
    {
        Log::info("SendOrderNotifications Listeners");
        $order = $event->order;

        $this->notifyAdmins($order);

        if ($order->is_instant_delivery) {
            $this->notifyDrivers($order);
        }
    }

    private function notifyAdmins($order): void
    {
        [$title, $body, $type] = $this->orderMessage($order);

        Admin::query()->chunk(100, function ($admins) use ($order, $title, $body, $type) {
            foreach ($admins as $admin) {
                $this->notificationService->send(
                    $admin,
                    $title,
                    $body,
                    [
                        'order_id' => $order->id,
                        'type' => $type,
                    ]
                );
            }
        });
    }

    private function notifyDrivers($order): void
    {
        Driver::where('is_active', true)
            ->chunk(100, function ($drivers) use ($order) {
                foreach ($drivers as $driver) {
                    $this->notificationService->send(
                        $driver,
                        'طلب توصيل فوري',
                        'يوجد طلب فوري جديد بحاجة لتوصيل',
                        [
                            'order_id' => $order->id,
                            'type' => 'instant',
                        ]
                    );
                }
            });
    }

    private function orderMessage($order): array
    {
        if ($order->is_instant_delivery) {
            return ['طلب فوري جديد', 'تم إنشاء طلب توصيل فوري', 'instant'];
        }

        return ['طلب توصيل جديد', 'تم إنشاء طلب توصيل غير فوري', 'scheduled'];
    }
}
