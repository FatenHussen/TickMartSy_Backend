<?php

namespace App\Http\Controllers\Admin\SaleCountry;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\SaleCountry\FilterRequest;
use App\Http\Requests\Admin\SaleCountry\StoreRequest;
use App\Http\Requests\Admin\SaleCountry\UpdateRequest;
use App\Services\Admin\SaleCountryService;

class SaleCountryCrudController extends BaseCRUDController
{
    public function __construct(SaleCountryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
