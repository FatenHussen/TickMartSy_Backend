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

    public function __construct(string $title, string $body, string $type)
    {
        $this->title = $title;
        $this->body  = $body;
        $this->type  = $type;
    }

    public function handle(NotificationService $notificationService)
    {
        if ($this->type === 'user' || $this->type === 'all') {
            User::chunk(100, function ($users) use ($notificationService) {
                foreach ($users as $user) {
                    $notificationService->send($user, $this->title, $this->body, [
                        'type' => 'admin'
                    ]);
                }
            });
        }

        if ($this->type === 'driver' || $this->type === 'all') {
            Driver::chunk(100, function ($drivers) use ($notificationService) {
                foreach ($drivers as $driver) {
                    $notificationService->send($driver, $this->title, $this->body, [
                        'type' => 'admin'
                    ]);
                }
            });
        }

        if ($this->type === 'vendor' || $this->type === 'all') {
            VendorUser::chunk(100, function ($vendors) use ($notificationService) {
                foreach ($vendors as $vendor) {
                    $notificationService->send($vendor, $this->title, $this->body, [
                        'type' => 'admin'
                    ]);
                }
            });
        }
    }
}
