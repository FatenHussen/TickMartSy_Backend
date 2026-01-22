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
        'discount',
        'brand_id',


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
    public function getPriceAfterDiscountAttribute()
    {
        if ($this->discount && $this->price) {
            return round($this->price - ($this->price * $this->discount / 100), 2);
        }
        return $this->price;
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
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
    public function brand()
    {
        return $this->belongsTo(Brand::class);
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
    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            ShopProductVariant::class,
            'product_variant_id',                 
            'shop_product_variant_id',   
            'id',
            'id'
        );
    }
    public function totalSoldQuantity()
    {
        return $this->completedOrderItems()->sum('quantity');
    }

    public function completedOrderItems()
    {
        return $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('order_status', 'completed');
            });
    }
    public function getSoldQuantityAttribute()
    {
        return $this->completedOrderItems()
            ->sum('order_items.quantity');
    }
}
