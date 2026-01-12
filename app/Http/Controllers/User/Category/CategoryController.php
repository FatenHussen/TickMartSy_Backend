<?php

namespace App\Http\Controllers\User\Category;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Category\FilterRequest;
use App\Services\User\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends BaseIndexController
{
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
