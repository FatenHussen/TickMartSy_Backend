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
        'country_id',
        'sale_country_id',
        'model',
        'price',
        'cost_price',
        'quantity',
        'unit',
        'warranty_period',
        'stock',
        'max_purchase_quantity',
        'barcode',
        'time_prepare',
        'delivery_time',
        'bought_with',
        'is_instant_delivery',
        'vendor_id',
        'discount',
        'discount_type',
        'brand_id',
        'approval_status',
        'rejection_reason',
        'is_visible',
        'is_active',
        'thumbnail',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_image',
        'expiry_date',
        'expiry_notified_at',
    ];

    public array $translatable = [
        'name',
        'description',
        'full_description',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'bought_with' => 'array',
        'time_prepare' => 'datetime:H:i',
        'approval_status' => \App\Enums\ProductApprovalStatus::class,
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'expiry_date' => 'date',
        'expiry_notified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $product) {
            if ($product->isDirty('expiry_date')) {
                $product->expiry_notified_at = null;
            }

            if (!$product->category_id) {
                return;
            }

            $isRestaurant = Category::query()
                ->whereKey($product->category_id)
                ->value('is_restaurant');

            if (!$isRestaurant) {
                return;
            }

            $product->country_id = null;
            $product->sale_country_id = null;
            $product->sku = null;
            $product->model = null;
            $product->barcode = null;
        });
    }

    public function getEffectiveDeliveryTimeAttribute(): ?string
    {
        // Tikmool vendor (id=1) always gets fixed delivery time
        if ($this->vendor_id === 1) {
            return '12-48 ساعة';
        }

        return $this->delivery_time;
    }

    public function getPriceAfterDiscountAttribute()
    {
        if (!$this->discount || !$this->price) {
            return $this->price;
        }

        if ($this->discount_type === 'percentage') {
            return round($this->price - ($this->price * $this->discount / 100), 2);
        } elseif ($this->discount_type === 'fixed') {
            return max(0, round($this->price - $this->discount, 2));
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

    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function saleCountry()
    {
        return $this->belongsTo(SaleCountry::class, 'sale_country_id');
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
