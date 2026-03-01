<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use App\Http\Resources\Permission\OneResource;
use App\Http\Resources\Permission\AllResource;
use Spatie\Permission\Models\Permission;

class PermissionService extends BaseService
{
    public function __construct(Permission $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['id', 'name'];
        $this->sortableFields  = ['id', 'name'];
    }
    public function getAll($filters = [], $config = [])
    {
        $filters['guard_name'] = 'admin';
        return parent::getAll($filters, $config);
    }
}
