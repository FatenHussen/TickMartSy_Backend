<?php

namespace App\Services\Admin;

use App\Models\CategoryAttribute;
use App\Http\Resources\Admin\Category\CategoryAttribute\OneResource;
use App\Http\Resources\Admin\Category\CategoryAttribute\AllResource;
use App\Services\BaseService;

class CategoryAttributeService extends BaseService
{
    protected $model      = CategoryAttribute::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $pagination = true;
    protected $relations = [
        'values',
    ];

    protected $syncRelations = [
        'values' => 'values',
    ];

    protected $searchableFields = [
        'id',
        'name',
        'category_id',
        'type'
    ];

    protected $sortableFields = [
        'id',
        'name',
        'type',
        'created_at',
    ];
}
