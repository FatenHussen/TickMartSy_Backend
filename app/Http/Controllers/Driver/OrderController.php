<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\AllResource;
use App\Http\Resources\Order\DriverOneResource as OneResource;
use App\Services\Driver\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $service) {}

    /* =======================
       📦 GET ORDERS
    ======================= */
    public function orders(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,out_delivery,delivered',
            'assigned_by' => 'nullable|in:admin,driver',

        ]);

        $res = $this->service->orders($data);

        return $this->sendResponse(
            data: AllResource::collection($res)
        );
    }

    public function order($orderId)
    {
        $res = $this->service->order($orderId);

        return $this->sendResponse(
            data: OneResource::make($res)
        );
    }

    /* =======================
       📦 GET ORDERS
    ======================= */
    public function ordersToAssigned(Request $request)
    {
        $data = $request->validate([
            'status' => 'nullable|in:pending,preparing',

        ]);

        $res = $this->service->ordersToAssigned($data);

        return $this->sendResponse(
            data: AllResource::collection($res)
        );
    }

    /* =======================
       ✅ ACCEPT ORDER
    ======================= */
    public function accept(int $orderId)
    {
        $order = $this->service->accept($orderId);

        return $this->sendResponse(
            data: OneResource::make($order),
            message: 'Order accepted successfully'
        );
    }

    /* =======================
       🚚 ITEM → OUT DELIVERY (instant)
    ======================= */
    public function itemOutDelivery(int $itemId)
    {
        $item = $this->service->itemOutDelivery($itemId);

        return $this->sendResponse(
            message: 'Item marked as out for delivery'
        );
    }

    /* =======================
       🚚 ORDER → OUT DELIVERY (non-instant)
    ======================= */
    public function orderOutDelivery(int $orderId)
    {
        $order = $this->service->orderOutDelivery($orderId);

        return $this->sendResponse(
            // data: OneResource::make($order),
            message: 'Order marked as out for delivery'
        );
    }

    /* =======================
       ✅ DELIVER ORDER (final)
    ======================= */

    public function deliver(int $orderId)
    {
        // $data = $request->validate([
        //     'code' => 'nullable|string'
        // ]);
        $this->service->deliver($orderId);


        return $this->sendResponse(
            // data: OneResource::make($order),
            message: 'Order delivered successfully'
        );
    }

    /* =======================
       📊 STATISTICS
    ======================= */
    public function statistics()
    {
        $data = $this->service->statistics();

        return $this->sendResponse(
            data: $data,
            message: 'Driver statistics retrieved successfully'
        );
    }
    public function currentOrder()
    {
        $order = $this->service->currentOrder();

        if (! $order) {
            return $this->sendResponse(
                message: 'No current order',
                data: null
            );
        }

        return $this->sendResponse(
            data: OneResource::make($order),
            message: 'Current order retrieved successfully'
        );
    }
}
