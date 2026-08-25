<?php

namespace App\Http\Controllers\User\CustomOrderRequest;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\User\CustomOrderRequest\FilterRequest;
use App\Http\Requests\User\CustomOrderRequest\StoreRequest;
use App\Services\User\CustomOrderRequestService;
use Illuminate\Http\Request;

class CustomOrderRequestController extends BaseCRUDController
{
    public function __construct(CustomOrderRequestService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->filterRequest = FilterRequest::class;
    }

    public function approve(int $id)
    {
        $res = $this->service->approve($id);

        return $this->sendResponse(
            data: $res,
            message: __('custom.custom_order_requests.approved_successfully')
        );
    }

    public function cancel(int $id)
    {
        $res = $this->service->cancel($id);

        return $this->sendResponse(
            data: $res,
            message: __('custom.custom_order_requests.cancelled_successfully')
        );
    }

    public function store(Request $request)
    {
        $data = app($this->createRequest)->validated();
        $res = $this->service->create($data);

        return $this->sendResponse(
            data: $res,
            message: __('custom.custom_order_requests.created_successfully')
        );
    }
}
