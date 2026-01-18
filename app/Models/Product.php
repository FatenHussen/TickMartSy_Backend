<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country',
        'model',
        'price',
        'price_after_discount',
        'quantity',
        'barcode',
        'time_prepare',
        'bought_with',
        'is_instant_delivery',
        'vendor_id',
    ];

    public array $translatable = [
        'name',
        'description',
        'full_description',
        'country',
    ];

    protected $casts = [
        'bought_with' => 'array',
        'time_prepare' => 'datetime:H:i',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categoryDetails()
    {
        return $this->hasMany(ProductCategoryDetail::class);
    }
    public function boughtWithProducts()
    {
        return $this->belongsToMany(Product::class, 'bought_with', 'product_id', 'bought_with_id');
    }

    public function extraDetails()
    {
        return $this->hasMany(ProductExtraDetail::class);
    }
    public function media()
    {
        return $this->morphMany(ProductMedia::class, 'mediable')
            ->where('collection', 'product')
            ->orderBy('order');
    }
    public function boughtWithProduct()
    {
        return Product::whereIn('id', $this->bought_with ?? [])->get();
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }

    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->title,
            'desc'     => $this->description,
            'image'    => $this->image_url,
            'price' => $this->price,
            'price_after_discount' => $this->price,
            'discount' => null,
            'top_badges' => [],
            'bottom_badges' => [],
        ];
    }
}
