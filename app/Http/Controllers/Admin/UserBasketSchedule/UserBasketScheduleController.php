<?php

namespace App\Http\Controllers\Admin\UserBasketSchedule;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserBasketSchedule\FilterRequest;
use App\Services\Admin\UserBasketScheduleService;
use Illuminate\Http\Request;

class UserBasketScheduleController extends BaseIndexController
{
    protected $service;

    public function __construct(UserBasketScheduleService $service)
    {
        $this->service = $service;
    }


    
}
