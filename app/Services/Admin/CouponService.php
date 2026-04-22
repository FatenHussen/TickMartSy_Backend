<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Coupon\AllResource;
use App\Http\Resources\Coupon\OneResource;
use App\Models\Coupon;
use App\Services\Base\NotificationService;
use App\Services\BaseService;

class CouponService extends BaseService
{
    public function __construct(Coupon $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['vendors', 'categories', 'products', 'shops', 'governorate', 'city'];
        $this->searchableFields = ['id', 'name', 'code'];
        $this->syncRelations = [
            'vendors'   => 'vendors',
            'categories' => 'categories',
            'products' => 'products',
            'shops' => 'shops',
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
            throw new CustomExceptionWithMessage('custom.coupons.affiliate_active_coupon');
        }

        $object = parent::create($data);

        $notificationService = app(NotificationService::class);

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

    public function update($id, array $data)
    {
        $coupon = Coupon::query()->findOrFail($id);
        $shouldNotifyAffiliate = false;

        if (array_key_exists('affiliate_id', $data) && !empty($data['affiliate_id'])) {
            $incomingAffiliateId = (string) $data['affiliate_id'];
            $currentAffiliateId = (string) $coupon->affiliate_id;

            if (!empty($coupon->affiliate_id) && $currentAffiliateId !== $incomingAffiliateId) {
                throw new CustomExceptionWithMessage(
                    'custom.coupons.affiliate_reassign_not_allowed',
                    422
                );
            }

            $shouldNotifyAffiliate = empty($coupon->affiliate_id);
        }

        $result = parent::update($id, $data);

        if ($shouldNotifyAffiliate) {
            $coupon->refresh();

            if ($coupon->markter) {
                app(NotificationService::class)->send(
                    recipient: $coupon->markter,
                    title: 'كوبون جديد',
                    body: 'لقد حصلت على كوبون جديد تم إسناده من قبل الأدمن.',
                    data: [
                        'type' => 'coupon',
                    ]
                );
            }
        }

        return $result;
    }
}
