<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Coupon\AllResource;
use App\Http\Resources\Coupon\OneResource;
use App\Models\Coupon;
use App\Models\Vendor;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if (!isset($data['affiliate_id']) || empty($data['affiliate_id'])) {
            return parent::create($data);
        }

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

        $object = parent::create($data);

        $notificationService = app(\App\Services\Base\NotificationService::class);

        $notificationService->send(
            recipient: $object->markter,
            title: 'كوبون جديد',
            body: "لقد حصلت على كوبون جديد تم اسناده من قبل الادمن",
            data: [
                'type' => 'coupon',
            ]
        );

        return $object;
    }
}
