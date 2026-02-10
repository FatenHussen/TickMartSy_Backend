<?php

namespace App\Services\Admin;

use App\Http\Resources\Coupon\AllResource;
use App\Http\Resources\Coupon\OneResource;
use App\Models\Coupon;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class CouponService extends BaseService
{
    public function __construct(Coupon $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['vendors', 'categories', 'products'];
        $this->searchableFields = ['id', 'name', 'code'];
        $this->syncRelations = [
            'vendors'   => 'vendors',
            'categories' => 'categories',
            'products' => 'products'
        ];
    }
}
