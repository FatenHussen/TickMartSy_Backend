<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Category\CategoryAttribute\FilterRequest;
use App\Http\Requests\Admin\Category\CategoryAttribute\StoreRequest;
use App\Http\Requests\Admin\Category\CategoryAttribute\UpdateRequest;
use App\Services\Admin\CategoryAttributeService;

class CategoryAttributeController extends BaseCRUDController
{
    public function __construct(CategoryAttributeService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
