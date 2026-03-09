<?php

namespace App\Services\User;

use App\Http\Resources\Country\AllResource;
use App\Http\Resources\Country\OneResource;
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
        $this->pagination = false ; // Probably no pagination for countries
    }
}