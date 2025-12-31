<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Admin\Category\AllResource;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->imageColumn = 'icon';
        $this->imageFolder = 'categories'; 
        $this->relations = ['parent', 'children'];
    }
}
