<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Color\AllResource;
use App\Http\Resources\Admin\Color\OneResource;
use App\Models\Color;
use App\Services\BaseService;

class ColorService extends BaseService
{
    public function __construct(Color $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['name', 'hex'];
        $this->sortableFields = ['id', 'hex', 'is_active', 'created_at'];
    }
}
