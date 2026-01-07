<?php

namespace App\Http\Controllers\User\Product;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Services\User\ProductService;
use Illuminate\Http\Request;
use App\Http\Requests\User\Product\FilterRequest;

class ProductController extends BaseIndexController
{
    public function __construct(ProductService $service)
    {
        $this->service = $service;
        $this->filterRequest =FilterRequest::class;
    }
}
