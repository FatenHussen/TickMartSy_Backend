<?php

namespace App\Services\User;

use App\Http\Resources\UserBasketSchedule\ScheduleResource;
use App\Models\Schedule;
use App\Services\BaseService;

class ScheduleService extends BaseService
{
    public function __construct(Schedule $model)
    {
        $this->model = $model;
        $this->collection = ScheduleResource::class;
    }
    public function query(array $filters = [])
    {
        $query = $this->model::query();
        $query = $this->queryBuilder($query, $filters);

        return $query->where('is_active', true);
    }
}
