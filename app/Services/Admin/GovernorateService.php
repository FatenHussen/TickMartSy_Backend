<?php

namespace App\Services\Admin;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource;

use App\Models\Governorate;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class GovernorateService extends BaseService
{
    public function __construct(Governorate $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'email'];
    }
}
