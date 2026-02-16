<?php

namespace App\Http\Controllers\Admin\Basket;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Basket\ScheduledBasket\FilterRequest;
use App\Http\Requests\Admin\Basket\ScheduledBasket\StoreRequest;
use App\Http\Requests\Admin\Basket\ScheduledBasket\UpdateRequest;
use App\Services\Admin\ScheduledBasketService;

class ScheduledBasketController extends BaseCRUDController
{
    public function __construct(ScheduledBasketService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
