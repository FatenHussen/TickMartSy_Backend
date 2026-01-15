<?php

namespace App\Services\User;

use App\Http\Resources\BasketSchedule\AllResource;
use App\Http\Resources\BasketSchedule\OneResource;
use App\Models\BasketSchedule;
use App\Services\BaseService;

class BasketScheduleService extends BaseService
{
    protected $model      = BasketSchedule::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'basket',
        'basket.category',
    ];

    protected $searchableFields = ['title'];
    protected $sortableFields   = ['id', 'type', 'is_active'];
    protected $pagination = true;
}
