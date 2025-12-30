<?php

namespace App\Http\Controllers\Admin\Role_Permission;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Role\StoreRequest;
use App\Http\Requests\Admin\Role\UpdateRequest;
use App\Services\Admin\RoleService;

class RoleCrudController extends BaseCRUDController
{
    public function __construct(RoleService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
