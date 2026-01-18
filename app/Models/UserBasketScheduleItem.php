<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBasketScheduleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_basket_id',
        'product_id',
        'shop_product_variant_id',
        'quantity',
    ];
    protected $appends = ['price'];


    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(UserBasketSchedule::class, 'user_basket_schedule_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ShopProductVariant::class, 'shop_product_variant_id');
    }
    public function getPriceAttribute()
    {
        if ($this->variant) {
            return $this->variant->price;
        }

        return $this->product?->price;
    }
}
