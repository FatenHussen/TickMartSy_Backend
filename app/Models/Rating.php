<?php

namespace App\Models;

use App\Enums\RateableType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'rating',
        'order_id',
        'comment',
        'image',
        'is_verified'
    ];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function getTypeAttribute(): ?string
    {
        return match ($this->rateable_type) {
            'App\Models\Product' => RateableType::PRODUCT->value,
            'App\Models\Brand' => RateableType::BRAND->value,
            'App\Models\Shop' => RateableType::SHOP->value,
            'App\Models\Delivery' => RateableType::DELIVERY->value,
            'App\Models\Recipe' => RateableType::RECIPE->value,
            'App\Models\Basket' => RateableType::BASKET->value,
            'App\Models\BasketSchedule' => RateableType::SCHEDULED_BASKET->value,
            default => null,
        };
    }
}
