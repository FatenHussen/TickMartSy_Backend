<?php

namespace App\Services\Admin;

use App\Models\CategoryDetail;
use App\Services\BaseService;
use App\Http\Resources\Admin\Category\CategoryDetail\OneResource;
use App\Http\Resources\Admin\Category\CategoryDetail\AllResource;

class CategoryDetailService extends BaseService
{
    protected $model      = CategoryDetail::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
    ];

    protected $searchableFields = [
        'id',
    ];

    protected $sortableFields = [
        'id',
        'created_at',
    ];
}
