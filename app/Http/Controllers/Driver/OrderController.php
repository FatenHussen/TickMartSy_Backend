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
            message: __('custom.driver.order_accepted')
        );
    }

    /* =======================
       🚚 ITEM → OUT DELIVERY (instant)
    ======================= */
    public function itemOutDelivery(int $itemId)
    {
        $item = $this->service->itemOutDelivery($itemId);

        return $this->sendResponse(
            message: __('custom.driver.item_out_delivery')
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
            message: __('custom.driver.order_out_delivery')
        );
    }

    public function shopOutDelivery(Request $request, int $orderId)
    {
        $data = $request->validate([
            'shop_id' => 'required|exists:shops,id',
        ]);

        $order = $this->service->shopOutDelivery($orderId, $data['shop_id']);

        return $this->sendResponse(
            // data: OneResource::make($order),
            message: __('custom.driver.shop_out_delivery')
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
            message: __('custom.driver.order_delivered')
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
            message: __('custom.driver.statistics_retrieved')
        );
    }
    public function currentOrder()
    {
        $order = $this->service->currentOrder();

        if (! $order) {
            return $this->sendResponse(
                message: __('custom.driver.no_current_order'),
                data: null
            );
        }

        return $this->sendResponse(
            data: OneResource::make($order),
            message: __('custom.driver.current_order_retrieved')
        );
    }
}
