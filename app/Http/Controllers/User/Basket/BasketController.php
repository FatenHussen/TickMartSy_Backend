<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Basket\FilterRequest;
use App\Services\User\BasketService;

class BasketController extends BaseIndexController
{
    public function __construct(BasketService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
