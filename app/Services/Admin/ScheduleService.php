<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Schedule\AllResource;
use App\Http\Resources\Admin\Schedule\OneResource;
use App\Models\Schedule;
use App\Services\BaseService;

class ScheduleService extends BaseService
{
    public function __construct(Schedule $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'interval_days', 'created_at'];
    }
}
