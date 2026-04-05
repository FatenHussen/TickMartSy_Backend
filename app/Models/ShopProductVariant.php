<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($shopVariant) {
            // 1. Remove from basket_items.shop_product_variant_ids JSON array
            BasketItem::whereJsonContains('shop_product_variant_ids', $shopVariant->id)
                ->each(function ($item) use ($shopVariant) {
                    $ids = collect($item->shop_product_variant_ids)
                        ->reject(fn($id) => $id == $shopVariant->id)
                        ->values()
                        ->toArray();

                    $item->update(['shop_product_variant_ids' => $ids ?: null]);
                });

            // 2. Delete basket_items where this is the primary shop_product_variant_id
            BasketItem::where('shop_product_variant_id', $shopVariant->id)->delete();

            // 3. Delete recipe_items linked to this variant
            RecipeItem::where('shop_product_variant_id', $shopVariant->id)->delete();
        });
    }

    protected $fillable = [
        'shop_id',
        'quantity',
        'price',
        'product_variant_id'
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'shop_product_variant_id');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }
}
