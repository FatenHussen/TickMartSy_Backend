<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Admin;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderStatusNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ إذا التغيير من Admin → بلغ المستخدم
        |--------------------------------------------------------------------------
        */
        if ($event->changedBy === 'admin') {
            $this->notificationService->send(
                $order->user,
                'تحديث حالة الطلب',
                "تم تحديث حالة طلبك رقم {$order->id}",
                [
                    'order_id' => $order->id,
                    'type'     => 'order',
                    'status' => $order->status
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ إذا التغيير من Driver → بلغ الأدمن
        |--------------------------------------------------------------------------
        */
        if ($event->changedBy === 'driver') {
            Admin::chunk(100, function ($admins) use ($order) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'تحديث من الدرايفر',
                        "تم تحديث الطلب رقم {$order->id} من قبل الدرايفر",
                        [
                            'order_id' => $order->id,
                            'type'     => 'order',
                            'status' => $order->status

                        ]
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ إذا الحالة صارت OUT_DELIVERY → بلغ المستخدم + الأدمن
        |--------------------------------------------------------------------------
        */
        if ($event->to === \App\Enums\OrderStatus::OUT_DELIVERY->value) {

            // المستخدم
            $this->notificationService->send(
                $order->user,
                'طلبك خرج للتوصيل',
                "طلبك رقم {$order->id} خرج للتوصيل",
                [
                    'order_id' => $order->id,
                    'type'     => 'order',
                    'status' => $order->status

                ]
            );

            // الأدمن
            Admin::chunk(100, function ($admins) use ($order) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'طلب خرج للتوصيل',
                        "الطلب رقم {$order->id} خرج للتوصيل",
                        [
                            'order_id' => $order->id,
                            'type'     => 'order',
                            'status' => $order->status
                        ]
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ إذا الحالة صارت DELIVERED → بلغ الأدمن فقط
        |--------------------------------------------------------------------------
        */
        if ($event->to === \App\Enums\OrderStatus::DELIVERED->value) {

            Admin::chunk(100, function ($admins) use ($order) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'تم تسليم الطلب',
                        "تم تسليم الطلب رقم {$order->id}",
                        [
                            'order_id' => $order->id,
                            'type'     => 'order',
                            'status' => $order->status
                        ]
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ إذا التغيير من User → بلغ الأدمن
        |--------------------------------------------------------------------------
        */
        if (
            $event->changedBy === 'user'
            && $event->to === \App\Enums\OrderStatus::CANCELLED->value
        ) {

            Admin::chunk(100, function ($admins) use ($order) {
                foreach ($admins as $admin) {
                    $this->notificationService->send(
                        $admin,
                        'إلغاء طلب',
                        "قام المستخدم بإلغاء الطلب رقم {$order->id}",
                        [
                            'order_id' => $order->id,
                            'type'     => 'order',
                            'status' => $order->status

                        ]
                    );
                }
            });
        }
    }
}
