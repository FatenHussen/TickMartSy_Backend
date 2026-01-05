<?php

namespace App\Http\Controllers\Admin\Governorate;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Area\FilterRequest;
use App\Http\Requests\Admin\Area\StoreRequest;
use App\Http\Requests\Admin\Area\UpdateRequest;
use App\Services\Admin\AreaService;

class AreaCrudController extends BaseCRUDController
{
    public function __construct(
        AreaService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
