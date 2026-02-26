<?php

namespace App\Listeners;

use App\Events\OrderItemStatusChanged;
use App\Models\Admin;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleOrderItemStatusNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(OrderItemStatusChanged $event): void
    {
        Log::info("HandleOrderItemStatusNotifications Listener");


        $item  = $event->item;
        $order = $item->order;

        Log::info('Order item status changed', [
            'changed_by' => $event->changedBy,
            'order_id'   => $order->id,
            'item_id'    => $item->id,
            'new_status' => $item->item_status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ إذا الدرايفر أخذ عنصر (instant delivery)
        |--------------------------------------------------------------------------
        */
        if ($event->changedBy === 'driver') {

            Admin::chunk(100, function ($admins) use ($order, $item) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'عنصر خرج للتوصيل',
                        "العنصر رقم {$item->id} من الطلب {$order->id} خرج للتوصيل",
                        [
                            'order_id' => $order->id,
                            'item_id'  => $item->id,
                            'type'     => 'order_item'
                        ]
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ إذا الأدمن غير حالة عنصر → بلغ المستخدم
        |--------------------------------------------------------------------------
        */
        if ($event->changedBy === 'admin') {

            $this->notificationService->send(
                $order->user,
                'تحديث عنصر في طلبك',
                "تم تحديث عنصر في الطلب رقم {$order->id}",
                [
                    'order_id' => $order->id,
                    'item_id'  => $item->id,
                    'type'     => 'order_item'
                ]
            );
        }
    }
}
