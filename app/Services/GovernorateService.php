<?php 

namespace App\Services;

use App\Http\Resources\User\GovernorateResource;
use App\Models\Governorate;

class GovernorateService extends BaseService
{
    public function __construct(Governorate $model)
    {
        $this->model = $model;
        $this->collection = GovernorateResource::class;
    }
}