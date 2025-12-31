<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\CategoryDetail\FilterRequest;
use App\Http\Requests\Admin\Category\CategoryDetail\StoreRequest;
use App\Http\Requests\Admin\Category\CategoryDetail\UpdateRequest;
use App\Services\Admin\CategoryDetailService;
use Illuminate\Http\Request;

class CategoryDetailController extends BaseCRUDController
{
    public function __construct(CategoryDetailService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
