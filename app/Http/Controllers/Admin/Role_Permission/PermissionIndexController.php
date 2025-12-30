<?php

namespace App\Http\Controllers\Admin\Role_Permission;

use App\Http\Controllers\BaseIndexController;
use App\Services\Admin\PermissionService;

class PermissionIndexController extends BaseIndexController
{
    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }
}
