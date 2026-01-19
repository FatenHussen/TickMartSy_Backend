<?php

namespace App\Http\Controllers\User\Brand;

use App\Http\Controllers\BaseCRUDController;
use App\Services\User\BrandService;

class BrandController extends BaseCRUDController
{
    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }
}
