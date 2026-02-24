<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
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

    public function create($data)
    {
        $affiliateId = $data['affiliate_id'];

        $hasActiveCoupon = Coupon::where('affiliate_id', $affiliateId)
            ->where('is_active', true)
            ->get()
            ->contains(function ($coupon) {
                return $coupon->isValid();
            });

        if ($hasActiveCoupon) {
            throw new CustomExceptionWithMessage('This affiliate already has an active coupon.');
        }
        return parent::create($data);
    }
}
