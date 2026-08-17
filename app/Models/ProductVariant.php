<?php

namespace App\Models;

use App\Http\Resources\Product\VariantAttributeResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['name'];

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'model',
        'barcode',
        'price',
        'quantity',
        'attributes_values_ids',
        'is_trend',
        'is_active',
    ];

    protected $casts = [
        'attributes_values_ids' => 'array',
        'is_active' => 'boolean',
        'price' => 'float',
        'quantity' => 'integer',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($productVariant) {
            // Cascade to basket_items
            $productVariant->basketItems()->delete();

            // Cascade to shop_product_variants
            $productVariant->shopVariants()->each(function ($shopVariant) {
                $shopVariant->delete();
            });

            // Media is kept on soft delete so a restore keeps its images
            if ($productVariant->isForceDeleting()) {
                $productVariant->media()->each(function ($media) {
                    (new \App\Services\Base\MediaService())->delete($media);
                });
            }
        });

        static::saved(function ($productVariant) {
            $productVariant->product?->syncQuantityFromVariants();
        });

        static::deleted(function ($productVariant) {
            $productVariant->product?->syncQuantityFromVariants();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopVariants()
    {
        return $this->hasMany(ShopProductVariant::class, 'product_variant_id');
    }

    public function basketItems()
    {
        return $this->hasMany(BasketItem::class, 'variant_id');
    }

    public function getAttributesWithDetails()
    {
        if (empty($this->attributes_values_ids)) {
            return [];
        }

        $attributeValues = AttributeValue::with(['categoryAttribute', 'color'])
            ->whereIn('id', $this->attributes_values_ids)
            ->get();

        return $attributeValues->map(function ($attributeValue) {
            $isColorType = ($attributeValue->categoryAttribute->type ?? null) === 'color';
            $attributeName = $attributeValue->categoryAttribute->name ?? null;

            if ($isColorType) {
                $colorName = $attributeValue->color?->getTranslation('name', app()->getLocale(), false)
                    ?: $attributeValue->color?->getTranslation('name', 'en', false)
                    ?: $attributeValue->color?->getTranslation('name', 'ar', false)
                    ?: $attributeValue->name;
                $hex = $attributeValue->color?->hex;

                return [
                    'id' => $attributeValue->id,
                    'name' => $colorName,
                    'display_name' => $hex ? ($colorName . ' (' . $hex . ')') : $colorName,
                    'hex' => $hex,
                    'color' => $attributeValue->color ? [
                        'id' => $attributeValue->color->id,
                        'name' => $colorName,
                        'hex' => $hex,
                    ] : null,
                    'category_attribute' => [
                        'id' => $attributeValue->categoryAttribute->id,
                        'name' => $attributeName,
                        'type' => $attributeValue->categoryAttribute->type ?? null,
                    ],
                ];
            }

            return [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'display_name' => $attributeValue->name,
                'hex' => null,
                'color' => null,
                'category_attribute' => [
                    'id' => $attributeValue->categoryAttribute->id,
                    'name' => $attributeName,
                    'type' => $attributeValue->categoryAttribute->type ?? null,
                ],
            ];
        });
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            null,
            'product_variant_id',
            'attribute_value_id'
        )->whereIn('attribute_values.id', $this->attributes_values_ids ?? []);
    }

    public function shops()
    {
        return $this->hasMany(ShopProductVariant::class, 'product_variant_id');
    }
    public function media()
    {
        return $this->morphMany(\App\Models\ProductMedia::class, 'mediable');
    }

    public function getImagesAttribute()
    {
        return $this->media->count()
            ? $this->media
            : $this->product->media;
    }

    public function getAttributesValuesAttribute()
    {
        return VariantAttributeResource::collection(
            AttributeValue::with(['categoryAttribute', 'color'])
                ->whereIn('id', $this->attributes_values_ids ?? [])
                ->get()
        );
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getFinalPriceAttribute(): float
    {
        // 1. Base price (سعر المتغير هو المصدر الوحيد للسعر)
        $price = (float) $this->price;

        // 2. Get product final discount
        $discount = $this->product?->final_discount;

        if (!$discount || !$discount['type'] || $discount['value'] <= 0) {
            return round($price, 2);
        }

        // 3. Apply discount
        if ($discount['type'] === 'percentage') {
            $price -= ($price * ($discount['value'] / 100));
        }

        if ($discount['type'] === 'fixed') {
            $price -= $discount['value'];
        }

        return (float) round(max(0, $price), 2);
    }

    public function getDiscountAttribute(): float
    {
        return (float) round(max(0, ((float) $this->price) - $this->final_price), 2);
    }

    public function getPriceAfterDiscountAttribute(): float
    {
        return $this->final_price;
    }
}
