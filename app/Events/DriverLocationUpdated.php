<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $lat,
        public string $lng
    ) {}

    public function broadcastOn(): Channel
    {
        Log::info('Broadcasting driver location for order ' . $this->orderId);
        return new Channel('order.' . $this->orderId);
    }


    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }
}
