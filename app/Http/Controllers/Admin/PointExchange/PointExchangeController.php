<?php

namespace App\Http\Controllers\Admin\PointExchange;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\PointExchange\FilterRequest;
use App\Http\Requests\Admin\PointExchange\UpdateRequest;
use App\Services\Admin\PointExchangeService;

class PointExchangeController extends BaseCRUDController
{
    public function __construct(PointExchangeService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
