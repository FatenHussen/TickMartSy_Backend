<?php

namespace App\Http\Controllers\Admin\Basket;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Basket\FilterRequest;
use App\Http\Requests\Admin\Basket\StoreRequest;
use App\Http\Requests\Admin\Basket\UpdateRequest;
use App\Services\Admin\BasketService;

class BasketController extends BaseCRUDController
{
    public function __construct(BasketService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}