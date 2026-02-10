<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\AllResource;
use App\Http\Resources\Order\OneResource;
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
            'status' => 'required|in:pending,preparing,out_delivery,delivered'
        ]);

        $res = $this->service->orders($data);

        return $this->sendResponse(
            data: AllResource::collection($res)
        );
    }

    /* =======================
       📦 GET ORDERS
    ======================= */
    public function ordersToAssigned(Request $request)
    {
        $data = $request->validate([
            'status' => 'nullable|in:pending,preparing'
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

    public function deliver(Request $request, int $orderId)
    {
        $data = $request->validate([
            'code' => 'required|string'
        ]);
        $this->service->deliver($orderId, $data['code']);


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
}
