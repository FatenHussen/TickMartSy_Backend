<?php

namespace App\Http\Controllers\User\Order;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Order\FilterRequest;
use App\Http\Requests\User\Order\StoreRequest;
use App\Http\Requests\User\Order\UpdateRequest;
use App\Http\Resources\Order\OneResource;
use App\Models\Order;
use App\Services\User\OrderService;
use Illuminate\Http\Request;

class OrderController extends BaseCRUDController
{

    public function __construct(OrderService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
    public function couponPreview(Request $request)
    {
        return   $this->service->couponPreview($request->all());
    }

    public function preview(StoreRequest $request)
    {
        return   $this->service->preview($request->all());
    }

    public function cancel($orderId)
    {
        return   $this->service->cancel($orderId);
    }
}
