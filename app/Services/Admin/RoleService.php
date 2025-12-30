<?php

namespace App\Services\Admin;

use Spatie\Permission\Models\Role;
use App\Services\BaseService;
use App\Http\Resources\Role\OneResource;
use App\Http\Resources\Role\AllResource;

class RoleService extends BaseService
{
    public function __construct(Role $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->syncRelations = [
            'permissions' => 'permissions'
        ];
        $this->searchableFields = ['id', 'name'];
        $this->sortableFields  = ['id', 'name'];
    }
}
