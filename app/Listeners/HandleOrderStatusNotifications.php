<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Admin;
use App\Models\Driver;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Enums\OrderStatus;

class HandleOrderStatusNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        Log::info("OrderStatusChanged Listener Triggered", [
            'order_id'   => $event->order->id,
            'from'       => $event->from,
            'to'         => $event->to,
            'changed_by' => $event->changedBy,
        ]);

        $order = $event->order;

        $this->notifyUser($order, $event->to);
        $this->notifyAdmins($order, $event);
        $this->notifyDrivers($order, $event);
        if ($event->from === null && $event->to === OrderStatus::PENDING->value) {
            $this->notifyAffiliate($order);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Notifications
    |--------------------------------------------------------------------------
    */
    private function notifyUser($order, string $status): void
    {
        $message = $this->userMessageForStatus($status, $order);

        if (!$message) {
            return;
        }

        $this->notificationService->send(
            $order->user,
            $message['title'],
            $message['body'],
            [
                'order_id' => (string) $order->id,
                'type'     => 'order',
                'status'   => (string) $order->status,
            ]
        );
    }

    private function userMessageForStatus(string $status, $order): ?array
    {
        return match ($status) {

            // OrderStatus::ACCEPTED->value => [
            //     'title' => 'تم قبول طلبك ✅',
            //     'body'  => "تم قبول طلبك رقم {$order->order_code}",
            // ],

            OrderStatus::PREPARING->value => [
                'title' => 'جاري تحضير طلبك 👨‍🍳',
                'body'  => "طلبك رقم {$order->order_code} قيد التحضير",
            ],

            OrderStatus::OUT_DELIVERY->value => [
                'title' => 'طلبك بالطريق 🚚',
                'body'  => "طلبك رقم {$order->order_code} خرج للتوصيل",
            ],

            OrderStatus::DELIVERED->value => [
                'title' => 'تم تسليم الطلب 🎉',
                'body'  => "تم تسليم طلبك رقم {$order->order_code}",
            ],

            OrderStatus::FAILDDELIVER->value => [
                'title' => 'فشل تسليم الطلب',
                'body'  => "فشل تسليم طلبك رقم {$order->order_code} و ستتم المراجعة من الادمن",
            ],

            OrderStatus::CANCELLED->value => [
                'title' => 'تم إلغاء الطلب ❌',
                'body'  => "تم إلغاء طلبك رقم {$order->order_code}",
            ],

            // OrderStatus::REJECTED->value => [
            //     'title' => 'تم رفض الطلب ⚠️',
            //     'body'  => "نأسف، تم رفض طلبك رقم {$order->order_code}",
            // ],

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Notifications
    |--------------------------------------------------------------------------
    */

    private function notifyAdmins($order, OrderStatusChanged $event): void
    {
        $message = $this->adminMessage($order, $event);

        if (!$message) {
            return;
        }

        Admin::chunk(100, function ($admins) use ($message, $order) {
            foreach ($admins as $admin) {
                $this->notificationService->send(
                    $admin,
                    $message['title'],
                    $message['body'],
                    [
                        'order_id' => (string) $order->id,
                        'type'     => 'order',
                        'status'   => (string) $order->status,
                    ]
                );
            }
        });
    }

    private function adminMessage($order, OrderStatusChanged $event): ?array
    {
        // 🆕 طلب جديد
        if (
            $event->from === null &&
            $event->to === OrderStatus::PENDING->value
        ) {
            return [
                'title' => 'طلب جديد 🆕',
                'body'  => "يوجد طلب جديد رقم {$order->order_code}",
            ];
        }

        // ❌ إلغاء من المستخدم
        if (
            $event->changedBy === 'user' &&
            $event->to === OrderStatus::CANCELLED->value
        ) {
            return [
                'title' => 'إلغاء طلب',
                'body'  => "قام المستخدم بإلغاء الطلب رقم {$order->order_code}",
            ];
        }

        // 🚗 تحديث من الدرايفر
        if ($event->changedBy === 'driver') {
            return [
                'title' => 'تحديث من الدرايفر',
                'body'  => "تم تحديث الطلب رقم {$order->order_code} من قبل الدرايفر",
            ];
        }

        // 🚚 خرج للتوصيل
        if ($event->to === OrderStatus::OUT_DELIVERY->value) {
            return [
                'title' => 'طلب خرج للتوصيل',
                'body'  => "الطلب رقم {$order->order_code} خرج للتوصيل",
            ];
        }

        // 🎉 تم التسليم
        if ($event->to === OrderStatus::DELIVERED->value) {
            return [
                'title' => 'تم تسليم الطلب',
                'body'  => "تم تسليم الطلب رقم {$order->order_code}",
            ];
        }

        //
        if ($event->to === OrderStatus::PREPARING->value && !$order->driver_id) {
            return [
                'title' => 'الطلب قيد التحضير',
                'body'  => "الطلب قيد التحضير {$order->order_code} ولا يوجد درايفر مسند للطلب",
            ];
        }

        // 
        if ($event->to === OrderStatus::FAILDDELIVER->value) {
            return [
                'title' => 'فشل في تسليم الطلب',
                'body'  =>  " فشل تسليم الطلب رقم {$order->order_code} ",
            ];
        }

        if ($event->to === OrderStatus::REJECTEDBYDELIVERY->value) {
            return [
                'title' => 'رفض استلام الديلفري الطلب',
                'body'  =>  "الطلب رقم {$order->order_code} تم رفض استلامه من الديلفري",
            ];
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Notifications
    |--------------------------------------------------------------------------
    */

    private function notifyDrivers($order, OrderStatusChanged $event): void
    {
        if (
            $event->changedBy === 'admin' &&
            $order->driver_id &&
            $event->from === $event->to
        ) {
            $this->notificationService->send(
                $order->driver,
                'تم تعيين طلب جديد لك 🚚',
                "تم تسليم الطلب رقم {$order->order_code} إليك من قبل لوحة الإدارة",
                [
                    'order_id' => (string) $order->id,
                    'type'     => 'order',
                    'status'   => (string) $order->status,
                ]
            );
        }

        // فقط عند إنشاء طلب جديد
        if (
            $event->from === null &&
            $event->to === OrderStatus::PENDING->value
        ) {
            Driver::where('is_active', true)->where('status', 'available')
                ->chunk(100, function ($drivers) use ($order) {
                    foreach ($drivers as $driver) {
                        $this->notificationService->send(
                            $driver,
                            'طلب جديد متاح 🚚',
                            "يوجد طلب جديد رقم {$order->order_code} بانتظار التوصيل",
                            [
                                'order_id' => (string) $order->id,
                                'type'     => 'order',
                                'status'   => (string) $order->status,
                            ]
                        );
                    }
                });
        }

        if (
            $event->to === OrderStatus::PREPARING->value && $order->driver_id
        ) {
            $this->notificationService->send(
                $order->driver,
                '  استعد الطلب قيد التحضير 🚚',
                "الطلب {$order->order_code} قيد التحضير و استعد لتسليمه",
                [
                    'order_id' => (string) $order->id,
                    'type'     => 'order',
                    'status'   => (string) $order->status,
                ]
            );
        }
    }

    private function notifyAffiliate($order): void
    {
        // تحقق من وجود ماركتر
        if (!$order->affiliate_id) {
            return;
        }

        $affiliate = \App\Models\User::where('affiliate_id', $order->affiliate_id)
            ->where('is_affiliate', true)
            ->where('affiliate_approved', true)
            ->first();

        if (!$affiliate) {
            return;
        }

        // إرسال الإشعار
        $this->notificationService->send(
            $affiliate,
            'طلب جديد من خلال رابطك 🛒',
            "تم إنشاء طلب جديد رقم {$order->order_code} عن طريق رابطك التسويقي.",
            [
                'order_id' => (string) $order->id,
                'type' => 'affiliate_order',
            ]
        );
    }
}
