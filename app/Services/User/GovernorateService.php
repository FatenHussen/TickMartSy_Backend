<?php 

namespace App\Services\User;

use App\Http\Resources\User\GovernorateResource;
use App\Models\Governorate;
use App\Services\BaseService;

class GovernorateService extends BaseService
{
    public function __construct(Governorate $model)
    {
        $this->model = $model;
        $this->collection = GovernorateResource::class;
    }
}