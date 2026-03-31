<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\User;
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
    public array $data;

    public function __construct(string $title, string $body, string $type, array $data = [])
    {
        $this->title = $title;
        $this->body  = $body;
        $this->type  = $type;
        $this->data  = $data;
    }

    public function handle(NotificationService $notificationService)
    {
        $payload = $this->data;

        if ($this->type === 'user' || $this->type === 'all') {
            User::query()->each(function (User $user) use ($notificationService, $payload) {
                $notificationService->send($user, $this->title, $this->body, $payload);
            });
        }

        if ($this->type === 'driver' || $this->type === 'all') {
            Driver::query()->each(function (Driver $driver) use ($notificationService, $payload) {
                $notificationService->send($driver, $this->title, $this->body, $payload);
            });
        }

        if ($this->type === 'vendor' || $this->type === 'all') {
            VendorUser::query()->each(function (VendorUser $vendor) use ($payload) {
                $vendorNotificationData = array_merge($payload, [
                    'title' => $this->title,
                    'body' => $this->body,
                ]);

                \App\Models\VendorNotification::create([
                    'vendor_user_id' => $vendor->id,
                    'notifiable_type' => VendorUser::class,
                    'notifiable_id' => $vendor->id,
                    'title' => $this->title,
                    'body' => $this->body,
                    'type' => 'admin',
                    'data' => json_encode($vendorNotificationData),
                ]);

                \Filament\Notifications\Notification::make()
                    ->title($this->title)
                    ->body($this->body)
                    ->success()
                    ->sendToDatabase($vendor);

                $tokens = $vendor->fcmTokens()->pluck('fcm_token')->filter()->values()->toArray();
                if (!empty($tokens)) {
                    \App\Jobs\SendVendorFcmNotificationJob::dispatch(
                        $tokens,
                        $this->title,
                        $this->body,
                        $vendorNotificationData
                    );
                }
            });
        }
    }
}
