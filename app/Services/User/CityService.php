<?php

namespace App\Services\User;

use App\Http\Resources\User\City\CityResource;
use App\Models\City;
use App\Services\BaseService;

class CityService extends BaseService
{
    public function __construct(City $model)
    {
        $this->model = $model;
        $this->pagination = false;
        $this->collection = CityResource::class;
    }
}
