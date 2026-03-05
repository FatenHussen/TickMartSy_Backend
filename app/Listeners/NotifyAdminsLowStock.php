<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\Admin;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsLowStock implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(LowStockDetected $event): void
    {
        $variant = $event->variant;

        Admin::chunk(100, function ($admins) use ($variant) {
            foreach ($admins as $admin) {
                $this->notificationService->send(
                    $admin,
                    'منتج أوشك على النفاذ ⚠️',
                    "المنتج رقم {$variant->id} تبقى منه {$variant->quantity} فقط",
                    [
                        'type' => 'low_stock',
                        'variant_id' => (string) $variant->id,
                        'quantity' => (string) $variant->quantity,
                    ]
                );
            }
        });
    }
}
