<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Services\User\BasketScheduleService;
use Illuminate\Http\Request;

class BasketScheduleController extends BaseIndexController
{
    public function __construct(BasketScheduleService $service)
    {
        $this->service = $service;
    }
}
