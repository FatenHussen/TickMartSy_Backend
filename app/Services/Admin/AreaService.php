<?php

namespace App\Services\Admin;

use App\Http\Resources\Area\AllResource;
use App\Http\Resources\Area\OneResource;
use App\Models\Admin;
use App\Models\Area;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class AreaService extends BaseService
{

    public function __construct(Area $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = OneResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'email'];
    }
}
