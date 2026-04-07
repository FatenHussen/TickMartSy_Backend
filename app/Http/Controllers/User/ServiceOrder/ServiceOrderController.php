<?php

namespace App\Http\Controllers\User\ServiceOrder;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\User\ServiceOrder\FilterRequest;
use App\Http\Requests\User\ServiceOrder\StoreRequest;
use App\Services\User\ServiceOrderService;

class ServiceOrderController extends BaseCRUDController
{
    public function __construct(ServiceOrderService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
