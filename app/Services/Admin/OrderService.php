<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Order\OneResource;
use App\Http\Resources\Order\AllResource;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;


class OrderService extends BaseService
{
    public function __construct(Order $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['driver', 'user', 'items'];
        $this->searchableFields = ['id'];
    }

    /* =======================
       🔄 ORDER STATUS TRANSITION
    ======================= */
    private function validateOrderStatusTransition(string $from, string $to): void
    {
        $allowed = [
            OrderStatus::PENDING->value => [
                OrderStatus::PREPARING->value,
            ],
            OrderStatus::PREPARING->value => [
                OrderStatus::OUT_DELIVERY->value,
            ],
            OrderStatus::OUT_DELIVERY->value => [
                OrderStatus::DELIVERED->value,
            ],
        ];

        if (! isset($allowed[$from]) || ! in_array($to, $allowed[$from])) {
            throw new CustomExceptionWithMessage(
                "Cannot change order status from {$from} to {$to}"
            );
        }
    }

    /* =======================
       🔄 ITEM STATUS TRANSITION
    ======================= */
    private function validateItemStatusTransition(string $from, string $to): void
    {
        $allowed = [
            OrderStatus::PENDING->value => [
                OrderStatus::PREPARING->value,
            ],
            OrderStatus::PREPARING->value => [
                OrderStatus::OUT_DELIVERY->value,
            ],
            OrderStatus::OUT_DELIVERY->value => [
                OrderStatus::DELIVERED->value,
            ],
        ];

        if (! isset($allowed[$from]) || ! in_array($to, $allowed[$from])) {
            throw new CustomExceptionWithMessage(
                "Cannot change item status from {$from} to {$to}"
            );
        }
    }

    /* =======================
       🔄 CHANGE ORDER STATUS
    ======================= */
    public function changeOrderStatus(int $orderId, string $newStatus)
    {
        return DB::transaction(function () use ($orderId, $newStatus) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status === OrderStatus::DELIVERED->value) {
                throw new CustomExceptionWithMessage(
                    'Delivered order cannot be changed'
                );
            }

            $this->validateOrderStatusTransition(
                $order->status,
                $newStatus
            );

            $order->update([
                'status' => $newStatus,
            ]);

            $order->items()->update([
                'item_status' => $newStatus,
            ]);

            return $order->fresh('items');
        });
    }

    /* =======================
       🧩 CHANGE ITEM STATUS
    ======================= */
    public function changeItemStatus(int $itemId, string $newStatus)
    {
        return DB::transaction(function () use ($itemId, $newStatus) {

            $item = OrderItem::with('order')
                ->lockForUpdate()
                ->findOrFail($itemId);

            $order = $item->order;

            if ($order->status === OrderStatus::DELIVERED->value) {
                throw new CustomExceptionWithMessage(
                    'Order already delivered'
                );
            }

            $this->validateItemStatusTransition(
                $item->item_status,
                $newStatus
            );

            $item->update([
                'item_status' => $newStatus,
            ]);

            // لو كل العناصر صاروا بنفس الحالة → حدّث الطلب
            if (
                $order->items()
                ->where('item_status', '!=', $newStatus)
                ->doesntExist()
            ) {
                $order->update([
                    'status' => $newStatus
                ]);
            }

            return $item->fresh();
        });
    }

    /* =======================
       🚚 ASSIGN DRIVER
    ======================= */
    public function assignDriver(int $orderId, int $driverId)
    {
        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->driver_id !== null) {
                throw new CustomExceptionWithMessage(
                    'Order already assigned'
                );
            }

            $order->update([
                'driver_id'   => $driverId,
                'assigned_by' => 'admin',
            ]);

            return $order;
        });
    }
}
