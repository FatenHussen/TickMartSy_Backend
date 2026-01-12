<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\BaseIndexController;
use App\Services\User\BasketService;

class BasketController extends BaseIndexController
{
    public function __construct(BasketService $service)
    {
        $this->service = $service;
    }
}
