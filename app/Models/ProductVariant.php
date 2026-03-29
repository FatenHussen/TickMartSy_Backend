<?php

namespace App\Models;

use App\Http\Resources\Product\VariantAttributeResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attributes_values_ids',
        'is_trend',
        'is_active',
    ];

    protected $casts = [
        'attributes_values_ids' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Cascade delete to related ShopProductVariants
        static::deleting(function ($productVariant) {
            $productVariant->shopVariants()->delete();
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

    public function getAttributesWithDetails()
    {
        if (empty($this->attributes_values_ids)) {
            return [];
        }

        $attributeValues = AttributeValue::with('categoryAttribute')
            ->whereIn('id', $this->attributes_values_ids)
            ->get();

        return $attributeValues->map(function ($attributeValue) {
            return [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'category_attribute' => [
                    'id' => $attributeValue->categoryAttribute->id,
                    'name' => $attributeValue->categoryAttribute->name,
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
        return VariantAttributeResource::collection(AttributeValue::whereIn('id', $this->attributes_values_ids ?? [])->get());
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
