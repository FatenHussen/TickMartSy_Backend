<?php

namespace App\Services\User;

use App\Http\Resources\User\Governorate\GovernorateResource;
use App\Models\Governorate;
use App\Services\BaseService;

class GovernorateService extends BaseService
{
    public function __construct(Governorate $model)
    {
        $this->model = $model;
        $this->pagination = false;
        $this->collection = GovernorateResource::class;
    }
}
