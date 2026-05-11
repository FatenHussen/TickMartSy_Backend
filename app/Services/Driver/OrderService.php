<?php

namespace App\Services\Driver;

use App\Enums\OrderStatus;
use App\Events\DriverAcceptedOrder;
use App\Events\OrderItemStatusChanged;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Shared\DriverCoverageService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly DriverCoverageService $driverCoverageService
    ) {}

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

    public function assignedOrders(array $data)
    {
        $driverId = auth('driver')->id();

        return Order::query()
            ->where('driver_id', $driverId)
            ->where('assigned_by', 'admin')
            ->when(
                isset($data['status']),
                fn($q) => $q->where('status', $data['status'])
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
        /** @var Driver $driver */
        $driver = $this->driverCoverageService->loadDriverWithCoverage(auth('driver')->id());

        $query = Order::query()
            ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PREPARING->value])
            ->where('is_instant_delivery', true)
            ->whereNull('driver_id');

        $this->driverCoverageService->applyInstantOrderCoverage($query, $driver);

        return $query->latest()->get();
    }

    private function instantOrderMatchesDriverCoverage(Order $order, Driver $driver): bool
    {
        return Order::query()
            ->whereKey($order->getKey())
            ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PREPARING->value])
            ->where('is_instant_delivery', true)
            ->whereNull('driver_id')
            ->tap(fn (Builder $q) => $this->driverCoverageService->applyInstantOrderCoverage($q, $driver))
            ->exists();
    }

    /* =======================
       ✅ ACCEPT ORDER (instant)
    ======================= */
    public function accept(int $orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::lockForUpdate()->findOrFail($orderId);

            /** @var Driver $driver */
            $driver = $this->driverCoverageService->loadDriverWithCoverage($driverId);

            if ($order->driver_id !== null) {
                throw new CustomExceptionWithMessage('custom.orders.already_assigned');
            }

            if (! $order->is_instant_delivery) {
                throw new CustomExceptionWithMessage('custom.orders.not_instant_delivery');
            }

            if (! $this->instantOrderMatchesDriverCoverage($order, $driver)) {
                throw new CustomExceptionWithMessage('custom.orders.not_in_driver_coverage');
            }

            $hasActiveInstantOrder = Order::where('driver_id', $driverId)
                ->where('is_instant_delivery', true)
                ->whereIn('status', [
                    OrderStatus::PENDING->value,
                    OrderStatus::PREPARING->value,
                    OrderStatus::OUT_DELIVERY->value,
                ])
                ->where('id', '!=', $orderId)
                ->exists();

            if ($hasActiveInstantOrder) {
                throw new CustomExceptionWithMessage('custom.driver.only_one_order_for_delivery');
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
       ✅ ACCEPT ORDER (instant)
    ======================= */
    public function reject(int $orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::lockForUpdate()->findOrFail($orderId);

            // ✅ تحقق أن الدرايفر هو نفس الشخص
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }


            if (! in_array($order->status, [
                OrderStatus::PENDING->value,
                OrderStatus::PREPARING->value
            ])) {
                throw new CustomExceptionWithMessage('custom.orders.invalid_order_state');
            }

            $order->update([
                'driver_id' => null,
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $order->status,
                OrderStatus::REJECTEDBYDELIVERY->value,
                'driver'
            );

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
                // OrderStatus::PENDING->value
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
       ✅ DELIVER ORDER (final)
    ======================= */
    public function faildDeliver(int $orderId)
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
                'status' => OrderStatus::FAILDDELIVER->value
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::FAILDDELIVER->value
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $oldStatus,
                OrderStatus::FAILDDELIVER->value,
                'driver'
            );
            return $order->fresh('items');
        });
    }

    public function returnedByUser(int $orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {
            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            if ($order->status !== OrderStatus::OUT_DELIVERY->value) {
                throw new CustomExceptionWithMessage('custom.orders.not_out_delivery');
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => OrderStatus::RETURNED_BY_USER->value,
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::RETURNED_BY_USER->value,
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $oldStatus,
                OrderStatus::RETURNED_BY_USER->value,
                'driver'
            );

            return $order->fresh('items');
        });
    }


    /* =======================
       📊 STATISTICS
    ======================= */
    public function statistics(array $filters = [])
    {
        $driver = auth('driver')->user();

        $period = $filters['period'] ?? 'day';
        [$start, $end] = $this->resolveStatisticsPeriod($period, $filters);

        $filteredQuery = $driver->completedOrders()
            ->whereBetween('delivered_at', [$start, $end]);

        $filteredDelivered = (clone $filteredQuery)->count();
        $deliverySum = (float) (clone $filteredQuery)->sum('delivery_price');
        $filteredEarnings = $this->calculateDriverEarnings($driver, $deliverySum);
        $filteredAvgSeconds = (clone $filteredQuery)
            ->whereNotNull('out_delivery_at')
            ->whereNotNull('delivered_at')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, out_delivery_at, delivered_at)'));
        $filteredAvgMinutes = $filteredAvgSeconds ? round($filteredAvgSeconds / 60, 2) : 0.0;

        return [
            'average_rating' => $driver->average_rating,
            'rate_percent' => (float) $driver->rate_per_order,
            'total_orders' => $driver->total_orders,
            'total_delivered' => $driver->total_delivered,
            'today_delivered' => $driver->today_delivered,
            'total_earnings' => $driver->total_earnings,
            'today_earnings' => $driver->today_earnings,
            'average_delivery_time_minutes' => $driver->average_delivery_time,
            'cancellation_rate_percent' => $driver->cancellation_rate,
            'filtered' => [
                'period' => $period,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'delivered_orders' => $filteredDelivered,
                'earnings' => $filteredEarnings,
                'average_delivery_time_minutes' => $filteredAvgMinutes,
            ],
        ];
    }

    private function resolveStatisticsPeriod(string $period, array $filters): array
    {
        $period = strtolower($period);

        if ($period === 'month') {
            $year = (int) ($filters['year'] ?? now()->year);
            $month = (int) ($filters['month'] ?? now()->month);
            $start = now()->setDate($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();

            return [$start, $end];
        }

        if ($period === 'custom') {
            $startDate = $filters['start_date'] ?? now()->toDateString();
            $endDate = $filters['end_date'] ?? $startDate;
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return [$start, $end];
        }

        $date = $filters['date'] ?? now()->toDateString();
        $start = Carbon::parse($date)->startOfDay();
        $end = $start->copy()->endOfDay();

        return [$start, $end];
    }

    private function calculateDriverEarnings(Driver $driver, float $deliverySum): float
    {
        $rate = (float) $driver->rate_per_order;
        $multiplier = $rate > 0 ? $rate / 100 : 0.0;

        return round($deliverySum * $multiplier, 2);
    }
    public function currentOrder()
    {
        $driverId = auth('driver')->id();

        return Order::with('items')
            ->where('driver_id', $driverId)
            ->whereIn('status', [
                OrderStatus::PREPARING->value,
                OrderStatus::OUT_DELIVERY->value

            ])
            ->where('start_todelivery', true)
            ->latest()
            ->first();
    }



    public function startToOutDelivery($orderId)
    {
        $driverId = auth('driver')->id();

        return DB::transaction(function () use ($orderId, $driverId) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            // ensure the driver is assigned to the order
            if ($order->driver_id !== $driverId) {
                throw new CustomExceptionWithMessage('custom.orders.not_your_order');
            }

            if ($order->status !== OrderStatus::PREPARING->value) {
                throw new CustomExceptionWithMessage('custom.orders.invalid_order_state');
            }

            $hasActiveOrder = Order::where('driver_id', $driverId)
                ->where('start_todelivery', true)
                ->whereIn('status', [
                    OrderStatus::PREPARING->value,
                    OrderStatus::OUT_DELIVERY->value

                ])->where('id', '!=', $orderId)
                ->exists();

            if ($hasActiveOrder) {
                throw new CustomExceptionWithMessage('custom.driver.only_one_order_for_delivery');
            }

            if (! $order->start_todelivery) {
                $order->update([
                    'start_todelivery' => true
                ]);
            }

            return $order->fresh('items');
        });
    }
}
