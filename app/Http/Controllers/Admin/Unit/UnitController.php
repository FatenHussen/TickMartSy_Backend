<?php

namespace App\Http\Controllers\Admin\Unit;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Unit\FilterRequest;
use App\Http\Requests\Admin\Unit\StoreRequest;
use App\Http\Requests\Admin\Unit\UpdateRequest;
use App\Services\Admin\UnitService;

class UnitController extends BaseCRUDController
{
    public function __construct(UnitService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
