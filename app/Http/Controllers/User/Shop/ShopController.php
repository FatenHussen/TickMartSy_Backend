<?php

namespace App\Http\Controllers\User\Shop;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Shop\ShopRequest;
use App\Services\User\ShopService;

class ShopController extends BaseIndexController
{
    public function __construct(ShopService $service)
    {
        $this->service = $service;
        $this->filterRequest = ShopRequest::class;
    }
}
