<?php

namespace App\Http\Controllers\Admin\ServiceOrder;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\ServiceOrder\ChangeStatusRequest;
use App\Http\Requests\Admin\ServiceOrder\FilterRequest;
use App\Http\Resources\ServiceOrder\OneResource;
use App\Services\Admin\ServiceOrderService;

class ServiceOrderController extends BaseIndexController
{
    public function __construct(ServiceOrderService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    public function changeStatus(ChangeStatusRequest $request, int $orderId)
    {
        $order = $this->service->changeStatus($orderId, $request->input('status'));

        return $this->sendResponse(
            data: new OneResource($order),
            message: __('custom.service_orders.status_updated_successfully')
        );
    }
}
