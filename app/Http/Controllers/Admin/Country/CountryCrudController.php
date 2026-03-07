<?php

namespace App\Http\Controllers\Admin\Country;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Country\FilterRequest;
use App\Http\Requests\Admin\Country\StoreRequest;
use App\Http\Requests\Admin\Country\UpdateRequest;
use App\Services\Admin\CountryService;

class CountryCrudController extends BaseCRUDController
{
    public function __construct(
        CountryService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}