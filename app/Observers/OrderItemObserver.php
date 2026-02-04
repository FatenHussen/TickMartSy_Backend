<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Enums\OrderStatus;

class OrderItemObserver
{
    public function updated(OrderItem $item)
    {
        $order = $item->order;
        if (! $order) return;

        // delivered ممنوع من item
        if ($item->item_status === OrderStatus::DELIVERED->value) {
            return;
        }

        $statuses = $order->items()
            ->select('item_status')
            ->distinct()
            ->pluck('item_status');

        if ($statuses->count() !== 1) {
            return;
        }

        $status = $statuses->first();

        if (in_array($status, [
            OrderStatus::PREPARING->value,
            OrderStatus::OUT_DELIVERY->value,
        ])) {
            if ($order->status !== $status) {
                $order->update(['status' => $status]);

                // 🔔 إشعارات حسب الحالة
            }
        }
    }
}
