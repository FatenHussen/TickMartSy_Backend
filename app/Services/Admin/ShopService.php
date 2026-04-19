<?php

namespace App\Services\Admin;

use App\Authorization\CityAccess;
use App\Models\Admin;
use App\Models\Shop;
use App\Models\Store;
use App\Services\Base\MediaService;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Shop\AllResource;
use App\Http\Resources\Shop\AdminOneResource;

class ShopService extends BaseService
{

    public function __construct(Shop $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['name', 'description'];
        $this->sortableFields   = ['id'];
        $this->relations = ['vendor', 'services', 'area', 'coupons'];
        $this->pagination = true;

        $this->syncRelations = [
            'services'   => 'service_ids',
            'badges'   => 'badges',
            'coupons'  => 'coupon_ids',
        ];

        $this->mediaCollections = [
            // 'logo' => [
            //     'collection' => 'logo',
            //     'type'       => 'single',
            // ],
            'cover_images' => [
                'collection' => 'cover',
                'type'       => 'multiple',
            ],
        ];

        $this->singleImages = [
            'logo'  => 'logo',
        ];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['shop_type'])) {
            match ($filters['shop_type']) {
                'restaurant' => $query->where('is_restaurant', true),
                'service_provider' => $query->where('is_service_provider', true),
                'store' => $query
                    ->where('is_restaurant', false)
                    ->where('is_service_provider', false),
                default => null,
            };

            unset($filters['shop_type']);
        }

        if (!empty($filters['shop_status'])) {
            $shopStatus = $filters['shop_status'];
            unset($filters['shop_status']);

            if ($shopStatus === 'active') {
                $query->where('is_active', true);
            }

            if ($shopStatus === 'inactive') {
                $query->where('is_active', false);
            }

            if (in_array($shopStatus, ['open', 'closed'], true)) {
                $day = strtolower(now()->englishDayOfWeek);
                $nowTime = now()->format('H:i');
                $dayPath = '$."' . $day . '"';

                $applyOpenCondition = function ($q) use ($dayPath, $nowTime): void {
                    $q->whereRaw("JSON_EXTRACT(working_hours, ?) IS NOT NULL", [$dayPath])
                        ->whereRaw(
                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(working_hours, ?)), 'false') IN ('false', '0')",
                            [$dayPath . '.closed']
                        )
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(working_hours, ?)) <= ?", [$dayPath . '.open', $nowTime])
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(working_hours, ?)) >= ?", [$dayPath . '.close', $nowTime]);
                };

                if ($shopStatus === 'open') {
                    $query->where($applyOpenCondition);
                }

                if ($shopStatus === 'closed') {
                    $query->where(function ($q) use ($applyOpenCondition): void {
                        $q->whereNot($applyOpenCondition);
                    });
                }
            }
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    protected function applyAdminCityRestriction(Builder $query): Builder
    {
        $admin = auth('admin')->user();
        if (! $admin instanceof Admin) {
            return $query;
        }

        return CityAccess::for($admin)->constrainShops($query);
    }
}
