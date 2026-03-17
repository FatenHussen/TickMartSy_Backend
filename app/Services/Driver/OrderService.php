<?php

namespace App\Services\Driver;

use App\Enums\OrderStatus;
use App\Events\DriverAcceptedOrder;
use App\Events\OrderItemStatusChanged;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /* =======================
       📦 GET ORDERS
    ======================= */
    public function orders(array $data)
    {
        $driverId = auth('driver')->id();

        return Order::query()
            ->where('status', $data['status'])
            ->where('driver_id', $driverId)
            ->when(
                isset($data['assigned_by']),
                fn($q) => $q->where('assigned_by', $data['assigned_by'])
            )
            ->latest()
            ->get();
    }

    public function order($orderId)
    {
        $driverId = auth('driver')->id();

        $order = Order::find($orderId);
        //check order to driver

        return $order;
    }

    public function ordersToAssigned(array $data)
    {

        return $query = Order::query()
            ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PREPARING->value])
            ->where('is_instant_delivery', true)
            ->whereNull('driver_id')
            ->latest()
            ->get();
    }

    /* =======================
       ✅ ACCEPT ORDER (instant)
    ======================= */
    public function accept(int $orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->driver_id !== null) {
                throw new CustomExceptionWithMessage('custom.orders.already_assigned');
            }

            if (! $order->is_instant_delivery) {
                throw new CustomExceptionWithMessage('custom.orders.not_instant_delivery');
            }

            if (! in_array($order->status, [
                OrderStatus::PENDING->value,
                OrderStatus::PREPARING->value
            ])) {
                throw new CustomExceptionWithMessage('custom.orders.invalid_order_state');
            }

            $order->update([
                'driver_id' => $driverId,
                'assigned_by' => 'driver',
            ]);


            DriverAcceptedOrder::dispatch($order);

            // 🔔 notify driver accepted

            return $order;
        });
    }

    /* =======================
   🚚 ITEM → OUT DELIVERY (instant)
======================= */
    public function itemOutDelivery(int $itemId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($itemId, $driverId) {

            $item = OrderItem::with('order')
                ->lockForUpdate()
                ->findOrFail($itemId);

            $order = $item->order;

            // ✅ تحقق أن الدرايفر هو نفس الشخص
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            // ✅ تحقق أن الطلب فوري
            // if (! $order->is_instant_delivery) {
            //     throw new CustomExceptionWithMessage('This item is not for instant delivery');
            // }

            // ✅ تحقق أن العنصر جاهز للتحرك
            if ($item->item_status !== OrderStatus::PREPARING->value) {
                throw new CustomExceptionWithMessage('custom.orders.item_not_ready');
            }

            $oldStatus = $item->item_status;

            $item->update([
                'item_status' => OrderStatus::OUT_DELIVERY->value
            ]);

            // OrderItemStatusChanged::dispatch(
            //     $item->fresh(),
            //     $oldStatus,
            //     OrderStatus::OUT_DELIVERY->value,
            //     'driver'
            // );


            return $item;
        });
    }


    /* =======================
   🚚 ORDER → OUT DELIVERY (non-instant)
======================= */
    public function orderOutDelivery(int $orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            // ✅ تحقق أن الطلب يخص هذا الدرايفر
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            // ✅ تحقق أن الطلب غير فوري
            // if ($order->is_instant_delivery) {
            //     throw new CustomExceptionWithMessage('This order is instant delivery, use itemOutDelivery instead');
            // }

            // ✅ تحقق أن الحالة صحيحة للتحويل
            $allowedStatuses = [
                OrderStatus::PREPARING->value,
                OrderStatus::PENDING->value // إذا احتجنا السماح للطلبات المعلقة
            ];

            if (!in_array($order->status, $allowedStatuses)) {
                throw new CustomExceptionWithMessage('custom.orders.status_not_valid_for_out_delivery');
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => OrderStatus::OUT_DELIVERY->value
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::OUT_DELIVERY->value
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $oldStatus,
                OrderStatus::OUT_DELIVERY->value,
                'driver'
            );

            return $order->fresh('items');
        });
    }

    /**
     * Mark all items of a specific shop as out for delivery
     */
    public function shopOutDelivery(int $orderId, int $shopId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $shopId, $driverId) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            if (in_array($order->status, [
                OrderStatus::CANCELLED->value,
                OrderStatus::DELIVERED->value,
            ], true)) {
                throw new CustomExceptionWithMessage('custom.orders.invalid_order_state');
            }

            $items = OrderItem::with('shopProductVariant')
                ->where('order_id', $orderId)
                ->whereHas('shopProductVariant', function ($query) use ($shopId) {
                    $query->where('shop_id', $shopId);
                })
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new CustomExceptionWithMessage('custom.orders.no_items_for_shop');
            }

            foreach ($items as $item) {
                if (!in_array($item->item_status, [
                    OrderStatus::PREPARING->value,
                    OrderStatus::OUT_DELIVERY->value,
                ], true)) {
                    throw new CustomExceptionWithMessage('custom.orders.item_not_ready');
                }
            }

            $updatedItems = [];

            foreach ($items as $item) {
                if ($item->item_status === OrderStatus::PREPARING->value) {
                    $oldStatus = $item->item_status;

                    $item->update([
                        'item_status' => OrderStatus::OUT_DELIVERY->value,
                    ]);

                    $updatedItems[] = $item->fresh();
                }
            }

            if (!empty($updatedItems)) {
                OrderItemStatusChanged::dispatch(
                    $updatedItems,
                    OrderStatus::PREPARING->value,
                    OrderStatus::OUT_DELIVERY->value,
                    'driver'
                );
            }

            $allOutDelivery = OrderItem::where('order_id', $orderId)
                ->where('item_status', '!=', OrderStatus::OUT_DELIVERY->value)
                ->doesntExist();

            if ($allOutDelivery && $order->status !== OrderStatus::OUT_DELIVERY->value) {
                $oldStatus = $order->status;
                $order->update([
                    'status' => OrderStatus::OUT_DELIVERY->value,
                ]);

                OrderStatusChanged::dispatch(
                    $order->fresh('items'),
                    $oldStatus,
                    OrderStatus::OUT_DELIVERY->value,
                    'driver'
                );
            }

            return $order->fresh('items');
        });
    }

    /* =======================
       ✅ DELIVER ORDER (final)
    ======================= */
    public function deliver(int $orderId)
    {
        $code = "";
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId, $code) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            // تحقق من صاحب الطلب
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            // تحقق من الحالة
            if ($order->status !== OrderStatus::OUT_DELIVERY->value) {
                throw new CustomExceptionWithMessage('custom.orders.not_out_delivery');
            }

            // // تحقق من كود التسليم
            // if ($order->delivery_code !== $code) {
            //     throw new CustomExceptionWithMessage('Invalid delivery code');
            // }

            $oldStatus = $order->status;

            $order->update([
                'status' => OrderStatus::DELIVERED->value
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::DELIVERED->value
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $oldStatus,
                OrderStatus::DELIVERED->value,
                'driver'
            );
            return $order->fresh('items');
        });
    }

    /* =======================
       📊 STATISTICS
    ======================= */
    public function statistics()
    {
        $driver = auth('driver')->user();
        $rate = $driver->rate_per_order;

        $orders = Order::where('driver_id', $driver->id)
            ->where('status', OrderStatus::DELIVERED->value)
            ->get(['delivery_price']);

        $earnings = $orders->sum(
            fn($o) =>
            $o->delivery_price * ($rate / 100)
        );

        return [
            'delivered_orders' => $orders->count(),
            'rate_percent' => $rate,
            'total_earnings' => round($earnings, 2),
        ];
    }
    public function currentOrder()
    {
        $driverId = auth('driver')->id();

        return Order::with('items')
            ->where('driver_id', $driverId)
            ->whereIn('status', [
                OrderStatus::OUT_DELIVERY->value
            ])
            ->latest()
            ->first();
    }
}
