<?php

namespace App\Services\User;

use App\Http\Resources\UserBasketSchedule\ScheduleResource;
use App\Models\Schedule;
use App\Services\BaseService;

class ScheduleService extends BaseService
{
    protected $model = Schedule::class;
    protected $resource = ScheduleResource::class;
    protected $collection = ScheduleResource::class;
    protected $pagination = false;
    protected $relations = [
        'badges',
        'scheduleImages',
    ];

    public function __construct(Schedule $model)
    {
        $this->model = $model;
    }

    public function query(array $filters = [])
    {
        $query = Schedule::query()->with(['badges', 'scheduleImages']);
        $allowed = array_intersect_key($filters, array_flip(['interval_days']));

        return $this->queryBuilder($query, $allowed);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        return $query->where('is_active', true)->orderBy('interval_days');
    }
}
