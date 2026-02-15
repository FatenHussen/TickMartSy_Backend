<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\FilterRequest;
use App\Http\Resources\Order\AllResource;
use App\Services\Admin\OrderService;
use Illuminate\Http\Request;

class OrderController extends BaseIndexController
{
    public function __construct(
        OrderService $service
    ) {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }



    /* =======================
       🔄 CHANGE ORDER STATUS
    ======================= */
    public function changeStatus(Request $request, int $orderId)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,out_delivery,delivered',
        ]);

        $order = $this->service->changeOrderStatus(
            $orderId,
            $data['status']
        );

        return $this->sendResponse(
            data: new AllResource($order),
            message: 'Order status updated successfully'
        );
    }

    /* =======================
       🚚 ASSIGN DRIVER
    ======================= */
    public function assignDriver(Request $request, int $orderId)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        $this->service->assignDriver($orderId, $data['driver_id']);

        return $this->sendResponse(
            message: 'Order assigned to driver successfully'
        );
    }

    /* =======================
       🧩 CHANGE ITEM STATUS
    ======================= */
    public function changeItemStatus(Request $request, int $itemId)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,out_delivery,delivered',
        ]);

        $item = $this->service->changeItemStatus(
            $itemId,
            $data['status']
        );

        return $this->sendResponse(
            data: $item,
            message: 'Item status updated successfully'
        );
    }
}
