<?php

namespace App\Services\Admin;

use App\Http\Resources\Service\AllResource;
use App\Http\Resources\Service\OneResource;
use App\Models\Admin;
use App\Models\Service;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ServiceService extends BaseService
{

    public function __construct(Service $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = OneResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
    }
}
