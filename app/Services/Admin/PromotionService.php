<?php

namespace App\Services\Admin;

use App\Http\Resources\Promotion\AllResource;
use App\Http\Resources\Promotion\OneResource;

use App\Models\Promotion;
use App\Services\BaseService;

class PromotionService extends BaseService
{
    public function __construct(Promotion $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
    }

    public  function fieldsForType(string $type): array
    {
        return match ($type) {
            'simple_discount' => [
                'name',
                'description',
                'discount_value',
                'discount_type',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            'spend_x_discount' => [
                'name',
                'description',
                'min_spend',
                'discount_value',
                'discount_type',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            'buy_x_get_y' => [
                'name',
                'description',
                'buy_quantity',
                'get_quantity',
                'gift_product_ids',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            'spend_x_get_gift' => [
                'name',
                'description',
                'min_spend',
                'gift_product_ids',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            'spend_x_get_points' => [
                'name',
                'description',
                'min_spend',
                'reward_points',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            'free_shipping' => [
                'name',
                'description',
                'min_spend',
                'is_active',
                'starts_at',
                'ends_at'
            ],
            default => ['name', 'description', 'is_active', 'starts_at', 'ends_at']
        };
    }
}
