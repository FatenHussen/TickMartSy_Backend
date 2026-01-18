<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\BasketSchedule\StoreRequest;
use App\Http\Requests\User\BasketSchedule\UpdateRequest;
use App\Services\User\UserBasketScheduleService;
use Illuminate\Http\Request;

class UserBasketScheduleController extends BaseCRUDController
{
    public function __construct(UserBasketScheduleService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
