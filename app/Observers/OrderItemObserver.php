<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Enums\OrderStatus;

class OrderItemObserver
{
    public function updated(OrderItem $orderItem)
    {
        $order = $orderItem->order;

        if (! $order) {
            return;
        }

        $statuses = $order->items()->pluck('item_status')->unique();

        // إذا كل العناصر بنفس الحالة
        if ($statuses->count() === 1) {
            $status = $statuses->first();

            match ($status) {
                'pending'      => $order->update(['status' => OrderStatus::PENDING->value]),
                'preparing'    => $order->update(['status' => OrderStatus::PREPARING->value]),
                'out_delivery' => $order->update(['status' => OrderStatus::OUT_DELIVERY->value]),
                'delivered'    => $order->update(['status' => OrderStatus::DELIVERED->value]),
                default        => null,
            };
        }
    }
}
