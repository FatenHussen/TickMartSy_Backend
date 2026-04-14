<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Brand\AllResource;
use App\Http\Resources\Admin\Brand\OneResource;
use App\Services\BaseService;
use App\Models\Brand;

class BrandService extends BaseService
{
    public function __construct(Brand $model)
    {
        $this->model        = $model;
        $this->resource     = OneResource::class;
        $this->collection   = AllResource::class;
        $this->singleImages = ['image'];
        $this->pagination   = true;
        $this->relations    = ['governorate', 'city', 'category', 'originCountry'];
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'created_at'];
    }
}
