<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Http\Resources\Product\AllResource;
use App\Models\Favorite;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country',
        'model',
        'price',
        'quantity',
        'barcode',
        'time_prepare',
        'bought_with',
        'is_instant_delivery',
        'vendor_id',
        'discount',
        'brand_id',
        'approval_status',
        'rejection_reason',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_image',
    ];

    public array $translatable = [
        'name',
        'description',
        'full_description',
        'country',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'bought_with' => 'array',
        'time_prepare' => 'datetime:H:i',
        'approval_status' => \App\Enums\ProductApprovalStatus::class,
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
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function getRatingBreakdown(): array
    {
        $breakdown = [];

        // Initialize all star ratings with 0 count
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = 0;
        }

        // Get actual rating counts
        $ratingCounts = $this->ratings()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Merge actual counts with initialized array
        foreach ($ratingCounts as $rating => $count) {
            $breakdown[(int)$rating] = (int)$count;
        }

        return $breakdown;
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
    public function shopVariants()
    {
        return $this->hasMany(ShopProductVariant::class);
    }
    public function categoryDetails()
    {
        return $this->hasMany(ProductCategoryDetail::class);
    }

    public function getBoughtWithProductsListAttribute()
    {
        if (!$this->bought_with || !is_array($this->bought_with)) {
            return collect([]);
        }
        return Product::whereIn('id', $this->bought_with)->get();
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

    public function productMedia()
    {
        return $this->media()->where('collection', ProductMedia::COLLECTION_PRODUCT);
    }

    public function mainMedia()
    {
        return $this->media()->where('collection', 'main')->first();
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }

    public function toSectionArray()
    {
        return AllResource::make($this);
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
                $q->where('status', 'completed');
            });
    }
    public function getSoldQuantityAttribute()
    {
        return $this->completedOrderItems()
            ->sum('order_items.quantity');
    }

    /**
     * Get shops that have this product (simple version - ID and name only)
     */
    public function getAvailableShops()
    {
        return Shop::whereHas('productVariants.productVariant', function ($query) {
            $query->where('product_id', $this->id);
        })
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function icons()
    {
        return $this->belongsToMany(Icon::class, 'icon_product');
    }


    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        // Product name & description
                        ->where("name->$locale", 'like', "%{$word}%")
                        ->orWhere("description->$locale", 'like', "%{$word}%")
                        ->orWhere('sku', 'like', "%{$word}%")
                        ->orWhere('barcode', 'like', "%{$word}%")

                        // Brand
                        ->orWhereHas('brand', function ($brandQuery) use ($word, $locale) {
                            $brandQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Category
                        ->orWhereHas('category', function ($catQuery) use ($word, $locale) {
                            $catQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Vendor
                        ->orWhereHas('vendor', function ($vendorQuery) use ($word, $locale) {
                            $vendorQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Variant attributes
                        // ->orWhereHas('variants.attributeValues', function ($attrQuery) use ($word, $locale) {
                        //     $attrQuery->where("name->$locale", 'like', "%{$word}%");
                        // })

                        // Extra details
                        // ->orWhereHas('extraDetails', function ($extraQuery) use ($word, $locale) {
                        //     $extraQuery
                        //         ->where("detail_key->$locale", 'like', "%{$word}%")
                        //         ->orWhere("detail_value->$locale", 'like', "%{$word}%");
                        // })
                    ;
                });
            }
        });
        // ->where('approval_status', 'approved');
    }

    public function getImageUrlAttribute()
    {
        $main = $this->mainMedia();
        if ($main) {
            return $main->url;
        }

        return $this->productMedia()->first()?->url;
    }
}
