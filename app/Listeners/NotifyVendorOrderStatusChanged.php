<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\Vendor\VendorNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyVendorOrderStatusChanged implements ShouldQueue
{
    public function __construct(
        private readonly VendorNotificationService $notificationService
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $this->notificationService->notifyOrderStatusChanged(
            $event->order,
            $event->from ?? 'unknown',
            $event->to
        );
    }
}
