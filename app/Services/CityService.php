<?php 

namespace App\Services;

use App\Http\Resources\User\CityResource;
use App\Models\City;

class CityService extends BaseService
{
    public function __construct(City $model)
    {
        $this->model = $model;
        $this->collection = CityResource::class;
    }
}