<?php

namespace App\Services\Admin;

use App\Models\PointRule;
use App\Services\BaseService;

class PointRuleService extends BaseService
{
    protected $model = PointRule::class;
    protected $searchableFields = ['code', 'title'];
    protected $sortableFields = ['id', 'code', 'title', 'type', 'value', 'is_active', 'created_at'];
}