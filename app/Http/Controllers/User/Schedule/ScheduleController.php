<?php

namespace App\Http\Controllers\User\Schedule;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Services\User\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends BaseIndexController
{
    public function __construct(ScheduleService $service)
    {
        $this->service = $service;
    }
}
