<?php

namespace App\Services\User;

use App\Http\Resources\Brand\OneResource;
use App\Http\Resources\Brand\AllResource;
use App\Models\Brand;
use App\Services\BaseService;

class BrandService extends BaseService
{
    public function __construct(Brand $model)
    {
        $this->model = $model;
        $this->collection = AllResource::class;
        $this->resource = OneResource::class;
    }


    public function query(array $filters)
    {
        $query = Brand::query()->latest();

        return $query;
    }
}
