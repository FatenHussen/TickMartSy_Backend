<?php

namespace App\Services\Driver;

use App\Enums\OrderStatus;
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

        $query = Order::query();

        return match ($data['status']) {
            'pending' => $query
                ->where('status', OrderStatus::PENDING->value)
                ->where('is_instant_delivery', true)
                ->whereNull('driver_id')
                ->latest()
                ->get(),

            default => $query
                ->where('status', $data['status'])
                ->where('driver_id', $driverId)
                ->latest()
                ->get(),
        };
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
                throw new CustomExceptionWithMessage('Order already assigned');
            }

            if (! $order->is_instant_delivery) {
                throw new CustomExceptionWithMessage('Not instant delivery');
            }

            if (! in_array($order->status, [
                OrderStatus::PENDING->value,
                OrderStatus::PREPARING->value
            ])) {
                throw new CustomExceptionWithMessage('Invalid order state');
            }

            $order->update([
                'driver_id' => $driverId
            ]);

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
                throw new CustomExceptionWithMessage('Not your order');
            }

            // ✅ تحقق أن الطلب فوري
            if (! $order->is_instant_delivery) {
                throw new CustomExceptionWithMessage('This item is not for instant delivery');
            }

            // ✅ تحقق أن العنصر جاهز للتحرك
            if ($item->item_status !== OrderStatus::PREPARING->value) {
                throw new CustomExceptionWithMessage('Item not ready');
            }

            // ✅ تحديث حالة العنصر
            $item->update([
                'item_status' => OrderStatus::OUT_DELIVERY->value
            ]);

            // 🔔 notify admin (item picked)

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
                throw new CustomExceptionWithMessage('Not your order');
            }

            // ✅ تحقق أن الطلب غير فوري
            if ($order->is_instant_delivery) {
                throw new CustomExceptionWithMessage('This order is instant delivery, use itemOutDelivery instead');
            }

            // ✅ تحقق أن الحالة صحيحة للتحويل
            $allowedStatuses = [
                OrderStatus::PREPARING->value,
                OrderStatus::PENDING->value // إذا احتجنا السماح للطلبات المعلقة
            ];

            if (!in_array($order->status, $allowedStatuses)) {
                throw new CustomExceptionWithMessage('Order status not valid for out delivery');
            }

            // ✅ تحديث حالة الطلب وكل العناصر
            $order->update([
                'status' => OrderStatus::OUT_DELIVERY->value
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::OUT_DELIVERY->value
            ]);

            // 🔔 notify admin + user

            return $order->fresh('items');
        });
    }

    /* =======================
       ✅ DELIVER ORDER (final)
    ======================= */
    public function deliver(int $orderId, string $code)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId, $code) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            // تحقق من صاحب الطلب
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('Not your order');
            }

            // تحقق من الحالة
            if ($order->status !== OrderStatus::OUT_DELIVERY->value) {
                throw new CustomExceptionWithMessage('Order not out delivery');
            }

            // تحقق من كود التسليم
            if ($order->delivery_code !== $code) {
                throw new CustomExceptionWithMessage('Invalid delivery code');
            }

            // تحديث حالة الطلب والعناصر
            $order->update([
                'status' => OrderStatus::DELIVERED->value
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::DELIVERED->value
            ]);

            // 🔔 notify admin + user

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
}
