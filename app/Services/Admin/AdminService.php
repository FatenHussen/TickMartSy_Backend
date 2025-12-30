<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\AllResource;
use App\Http\Resources\Admin\OneResource;
use App\Models\Admin;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class AdminService extends BaseService
{

    public function __construct(Admin $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'email'];
    }
}
