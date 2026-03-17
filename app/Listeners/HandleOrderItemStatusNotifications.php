<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
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


        $items = $event->items;
        $order = $items[0]->order; // كلهم نفس الطلب

        Log::info('Order item status changed', [
            'changed_by' => $event->changedBy,
            'order_id'   => $order->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ إذا الدرايفر أخذ عنصر (instant delivery)
        |--------------------------------------------------------------------------
        */
        if ($event->changedBy === 'driver' && $event->to == OrderStatus::OUT_DELIVERY->value) {

            $count = count($items);
            $shopName = $items[0]->shopProductVariant->shop->name;

            Admin::chunk(100, function ($admins) use ($order, $count, $shopName) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'تم استلام الطلب من المحل',
                        "تم استلام {$count} عنصر من {$shopName} للطلب {$order->order_code}",
                        [
                            'order_id' => (string) $order->id,
                            'type'     => 'order_items_group'
                        ]
                    );
                }
            });
        }


        if (
            $event->changedBy == 'vendor' &&
            $event->to == OrderStatus::PREPARING->value
        ) {

            $items = $event->items;
            $order = $items[0]->order;

            $count = count($items);
            $shopName = $items[0]->shopProductVariant->shop->name;

            /*
            |--------------------------------------------------
            | 1️⃣ إشعار لكل الأدمن
            |--------------------------------------------------
            */
            Admin::chunk(100, function ($admins) use ($order, $count, $shopName) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'المحل بدأ التحضير',
                        "المحل {$shopName} بدأ تحضير {$count} عنصر للطلب {$order->order_code}",
                        [
                            'order_id' => (string) $order->id,
                            'type'     => 'order_items_group'
                        ]
                    );
                }
            });

            /*
            |--------------------------------------------------
            | 2️⃣ إشعار للدرايفر
            |--------------------------------------------------
            */
            if ($order->driver) {
                $this->notificationService->send(
                    $order->driver,
                    'طلبك قيد التحضير',
                    "المحل {$shopName} بدأ تحضير الطلب {$order->order_code}",
                    [
                        'order_id' => (string) $order->id,
                        'type'     => 'order'
                    ]
                );
            }
        }
    }
}
