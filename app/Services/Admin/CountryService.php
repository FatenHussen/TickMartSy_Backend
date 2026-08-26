<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Country\AllResource;
use App\Http\Resources\Admin\Country\OneResource;
use App\Models\Country;
use App\Services\BaseService;

class CountryService extends BaseService
{
    protected $searchableFields = ['name'];

    public function __construct(Country $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = [];
        // Full list for product/brand origin dropdowns (no pagination)
        $this->pagination = false;
    }
}