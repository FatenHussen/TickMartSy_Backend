<?php

namespace App\Http\Controllers\Admin\Governorate;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\City\FilterRequest;
use App\Http\Requests\Admin\City\StoreRequest;
use App\Http\Requests\Admin\City\UpdateRequest;
use App\Services\Admin\CityService;

class CityCrudController extends BaseCRUDController
{
    public function __construct(
        CityService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
