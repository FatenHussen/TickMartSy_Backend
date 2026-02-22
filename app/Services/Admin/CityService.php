<?php

namespace App\Services\Admin;

use App\Http\Resources\City\AllResource;
use App\Http\Resources\City\OneResource;
use App\Models\Admin;
use App\Models\Area;
use App\Models\City;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class CityService extends BaseService
{

    public function __construct(City $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'email'];
    }
}
