<?php

namespace App\Http\Controllers\Admin\Governorate;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Governorate\FilterRequest;
use App\Http\Requests\Admin\Governorate\StoreRequest;
use App\Http\Requests\Admin\Governorate\UpdateRequest;
use App\Services\Admin\GovernorateService;

class GovernorateCrudController extends BaseCRUDController
{
    public function __construct(
        GovernorateService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
