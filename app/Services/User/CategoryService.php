<?php

namespace App\Services\User;

use App\Http\Resources\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Category\AllResource;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['parent', 'children'];
        $this->pagination = true;
    }
}
