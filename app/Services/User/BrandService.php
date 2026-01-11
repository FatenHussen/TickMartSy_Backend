<?php

namespace App\Services\User;

use App\Http\Resources\Admin\Brand\OneResource;
use App\Http\Resources\User\City\CityResource;
use App\Models\Brand;
use App\Models\City;
use App\Services\BaseService;

class BrandService extends BaseService
{
    public function __construct(Brand $model)
    {
        $this->model = $model;
        $this->collection = OneResource::class;
    }


    public function query(array $filters)
    {
        $query = Brand::query()->latest();

        return $query;
    }
}
