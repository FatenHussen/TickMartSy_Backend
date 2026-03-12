<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Driver;
use App\Models\VendorUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Base\NotificationService;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $title;
    public string $body;
    public string $type;
    public string $is_fixed;

    public function __construct(string $title, string $body, string $type, $is_fixed = 0)
    {
        $this->title = $title;
        $this->body  = $body;
        $this->type  = $type;
        $this->is_fixed  = $is_fixed;
    }

    public function handle(NotificationService $notificationService)
    {
        if ($this->type === 'user' || $this->type === 'all') {
            User::query()->each(function (User $user) use ($notificationService) {
                $notificationService->send($user, $this->title, $this->body, [
                    'type' => 'admin',
                    'is_fixed' => $this->is_fixed
                ]);
            });
        }

        if ($this->type === 'driver' || $this->type === 'all') {
            Driver::query()->each(function (Driver $driver) use ($notificationService) {
                $notificationService->send($driver, $this->title, $this->body, [
                    'type' => 'admin',
                ]);
            });
        }

        if ($this->type === 'vendor' || $this->type === 'all') {
            VendorUser::query()->each(function (VendorUser $vendor) {
                // حفظ الإشعار في قاعدة البيانات مع notifiable fields
                \App\Models\VendorNotification::create([
                    'vendor_user_id' => $vendor->id,
                    'notifiable_type' => \App\Models\VendorUser::class,
                    'notifiable_id' => $vendor->id,
                    'title' => $this->title,
                    'body' => $this->body,
                    'type' => 'admin',
                    'data' => json_encode([
                        'type' => 'admin',
                        'is_fixed' => $this->is_fixed,
                    ]),
                ]);

                // إرسال FCM
                $tokens = $vendor->fcmTokens()->pluck('fcm_token')->filter()->values()->toArray();
                if (!empty($tokens)) {
                    \App\Jobs\SendVendorFcmNotificationJob::dispatch(
                        $tokens,
                        $this->title,
                        $this->body,
                        [
                            'type' => 'admin',
                            'is_fixed' => (string) $this->is_fixed,
                        ]
                    );
                }
            });
        }
    }
}
