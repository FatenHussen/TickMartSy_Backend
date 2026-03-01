<?php

namespace App\Http\Controllers\Admin\Schedule;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Schedule\StoreRequest;
use App\Http\Requests\Admin\Schedule\UpdateRequest;
use App\Services\Admin\ScheduleService;

class ScheduleCrudController extends BaseCRUDController
{
    public function __construct(ScheduleService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
