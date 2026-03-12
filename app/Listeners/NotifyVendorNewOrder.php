<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Vendor\VendorNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyVendorNewOrder implements ShouldQueue
{
    public function __construct(
        private readonly VendorNotificationService $notificationService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->notificationService->notifyNewOrder($event->order);
    }
}
