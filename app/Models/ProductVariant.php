<?php

namespace App\Models;

use App\Http\Resources\Product\VariantAttributeResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'attributes_values_ids',
        'is_trend'
    ];

    protected $casts = [
        'attributes_values_ids' => 'array',
    ];

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
