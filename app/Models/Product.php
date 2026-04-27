<?php

namespace App\Models;

use App\Http\Resources\Product\AllResource;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;

    protected $fillable = [
        'product_number',
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
        'unit_id',
        'warranty_period',
        'stock',
        'max_purchase_quantity',
        'barcode',
        'time_prepare',
        'delivery_time',
        'bought_with',
        'is_instant_delivery',
        'vendor_id',
        'flash_sale_id',
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
        'flash_sale_id' => 'integer',
        'unit_id' => 'integer',
        'expiry_date' => 'date',
        'expiry_notified_at' => 'datetime',
    ];

    protected bool $activeFlashSaleResolved = false;
    protected ?FlashSale $activeFlashSaleCache = null;

    public function setExpiryDateAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['expiry_date'] = null;
            return;
        }

        if (is_string($value)) {
            $normalized = trim($value);

            if ($normalized === '' || $normalized === '"' || $normalized === "'" || strtolower($normalized) === 'null') {
                $this->attributes['expiry_date'] = null;
                return;
            }

            try {
                $this->attributes['expiry_date'] = Carbon::parse($normalized)->format('Y-m-d');
                return;
            } catch (\Throwable $e) {
                $this->attributes['expiry_date'] = null;
                return;
            }
        }

        try {
            $this->attributes['expiry_date'] = Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            $this->attributes['expiry_date'] = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Model Hooks
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Accessors (Pricing & Discounts)
    |--------------------------------------------------------------------------
    */
    public function getFinalDiscountAttribute(): array
    {
        if ($flash = $this->getFlashSaleDiscountDetails()) {
            return $flash;
        }

        if ($product = $this->getProductDiscountDetails()) {
            return $product;
        }

        return [
            'source' => 'none',
            'type' => null,
            'value' => 0,
        ];
    }

    public function getPriceAfterDiscountAttribute()
    {
        if (!$this->price) {
            return $this->price;
        }

        $finalDiscount = $this->final_discount;

        if (!$finalDiscount['type'] || $finalDiscount['value'] <= 0) {
            return $this->price;
        }

        return $this->applyDiscount(
            $this->price,
            $finalDiscount['type'],
            $finalDiscount['value']
        );
    }

    public function getEffectiveDeliveryTimeAttribute(): ?string
    {
        // Tikmool vendor (id=1) always gets fixed delivery time
        if ($this->vendor_id === 1) {
            return '12-48 ساعة';
        }

        return $this->delivery_time;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getSoldQuantityAttribute()
    {
        return $this->completedOrderItems()->sum('order_items.quantity');
    }

    public function getBoughtWithProductsListAttribute()
    {
        if (!$this->bought_with || !is_array($this->bought_with)) {
            return collect([]);
        }

        return Product::whereIn('id', $this->bought_with)->get();
    }

    public function getImageUrlAttribute()
    {
        $main = $this->mainMedia();
        if ($main) {
            return $main->url;
        }

        return $this->productMedia()->first()?->url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, '/storage/')) {
            return asset(ltrim($this->thumbnail, '/'));
        }

        return asset('storage/' . ltrim($this->thumbnail, '/'));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Ratings)
    |--------------------------------------------------------------------------
    */
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    /*
    |--------------------------------------------------------------------------
    | Discount Helpers
    |--------------------------------------------------------------------------
    */
    protected function getFlashSaleDiscountDetails(): ?array
    {
        $flashSale = $this->resolveActiveFlashSale();

        if (!$flashSale || !$flashSale->discount) {
            return null;
        }

        return [
            'source' => 'flash_sale',
            'type' => $this->normalizeDiscountType($flashSale->discount_type),
            'value' => (float) $flashSale->discount,
        ];
    }

    protected function getProductDiscountDetails(): ?array
    {
        if (!$this->discount || !$this->discount_type || $this->discount_type === 'none') {
            return null;
        }

        return [
            'source' => 'product',
            'type' => $this->normalizeDiscountType($this->discount_type),
            'value' => (float) $this->discount,
        ];
    }

    protected function resolveActiveFlashSale(): ?FlashSale
    {
        if ($this->activeFlashSaleResolved) {
            return $this->activeFlashSaleCache;
        }

        $flashSale = $this->flashSale;

        $isValidFlashSale = $flashSale
            && $flashSale->is_active
            && $flashSale->end_date
            && $flashSale->end_date->greaterThan(now());

        $this->activeFlashSaleCache = $isValidFlashSale ? $flashSale : null;
        $this->activeFlashSaleResolved = true;

        return $this->activeFlashSaleCache;
    }

    protected function normalizeDiscountType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        return $type === 'percent' ? 'percentage' : $type;
    }

    protected function applyDiscount(float $price, string $type, float $value): float
    {
        if ($type === 'percentage') {
            return round($price - ($price * ($value / 100)), 2);
        }

        if ($type === 'fixed') {
            return max(0, round($price - $value, 2));
        }

        return $price;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitOption()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
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

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
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

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function icons()
    {
        return $this->belongsToMany(Icon::class, 'icon_product');
    }

    /*
    |--------------------------------------------------------------------------
    | Business Helpers
    |--------------------------------------------------------------------------
    */
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
            $breakdown[(int) $rating] = (int) $count;
        }

        return $breakdown;
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

    public function toSectionArray()
    {
        return AllResource::make($this);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
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
}
