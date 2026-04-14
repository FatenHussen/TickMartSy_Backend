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
            'status' => 'required|in:pending,preparing,out_delivery,delivered,cancelled',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,cancelled',
        ]);

        $order = $this->service->changeOrderStatus(
            $orderId,
            $data['status'],
            $data['rejection_reason'] ?? null
        );

        return $this->sendResponse(
            data: new AllResource($order),
            message: __('custom.orders.status_updated_successfully')
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
            message: __('custom.orders.assigned_to_driver_successfully')
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
            message: __('custom.orders.item_status_updated_successfully')
        );
    }
}
