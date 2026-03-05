<?php

namespace App\Services\Admin;

use App\Models\PointRule;
use App\Services\BaseService;
use App\Http\Resources\Admin\PointRule\PointRuleResource;

class PointRuleService extends BaseService
{
    protected $model = PointRule::class;
    protected $resource = PointRuleResource::class;
    protected $collection = PointRuleResource::class;
    protected $searchableFields = ['code', 'title'];
    protected $sortableFields = ['id', 'code', 'title', 'type', 'value', 'is_active', 'created_at'];
}
