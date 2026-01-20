<?php

namespace App\Http\Controllers\User\Brand;

use App\Http\Controllers\BaseIndexController;
use App\Services\User\BrandService;

class BrandController extends BaseIndexController
{
    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }
}
