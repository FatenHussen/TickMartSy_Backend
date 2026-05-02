<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\ProductMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;
use App\Traits\LogsActivity;

class Basket extends Model implements Sectionable
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'num_varieties',
        'offer_ends_at',
        'price',
        'discount',
        'discount_type',
        'rating',
        'num_sold',
        'image',
        'is_schedule',
        'is_active',
        'delivery_price'
    ];

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function popupCampaigns(): MorphToMany
    {
        return $this->morphToMany(PopupCampaign::class, 'attachable', 'popup_campaign_attachables');
    }

    public $translatable = ['name', 'description'];

    protected $casts = [
        'offer_ends_at' => 'date',
        'is_schedule' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'basket_category');
    }

    public function items()
    {
        return $this->hasMany(BasketItem::class, 'basket_id', 'id');
    }

    public function getCalculatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute()
    {
        $total = $this->calculated_price;

        // If basket is scheduled and has a default schedule, use its discount
        if ($this->is_schedule && $this->defaultSchedule) {
            $schedule = $this->defaultSchedule;
            if ($schedule->discount_type === 'percentage') {
                return $total * ($schedule->discount_value / 100);
            } else {
                return $schedule->discount_value ?? 0;
            }
        }

        // Otherwise use basket's own discount
        if ($this->discount_type === 'percentage') {
            return $total * ($this->discount / 100);
        } else {
            return $this->discount;
        }
    }

    public function getFinalPriceAttribute()
    {
        return $this->calculated_price - $this->discount_amount;
    }

    protected static function booted()
    {
        static::saving(function ($basket) {
            $basket->price = $basket->calculated_price;
        });
    }
    public function  getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        $firstMedia = $this->relationLoaded('basketImages')
            ? $this->basketImages->first()
            : $this->basketImages()->first();

        return $firstMedia?->url;
    }

    public function getImageUrlsAttribute(): array
    {
        $urls = [];

        if ($this->image) {
            $urls[] = asset('storage/' . $this->image);
        }

        $mediaUrls = ($this->relationLoaded('basketImages')
            ? $this->basketImages
            : $this->basketImages()->get())
            ->map(fn($media) => $media->url)
            ->values()
            ->all();

        return array_values(array_unique(array_merge($urls, $mediaUrls)));
    }

    public function media(): MorphMany
    {
        return $this->morphMany(ProductMedia::class, 'mediable');
    }

    public function basketImages(): MorphMany
    {
        return $this->media()
            ->where('collection', 'basket')
            ->orderBy('order');
    }
    public function schedules()
    {
        return $this->hasMany(BasketSchedule::class);
    }

    public function activeSchedule(): HasMany
    {
        return $this->hasMany(BasketSchedule::class)->where('is_active', true);
    }

    public function defaultSchedule()
    {
        return $this->hasOne(BasketSchedule::class)->where('is_default', true);
    }
    public function toSectionArray(): array
    {
        $nextDelivery = null;

        // Use default schedule for next delivery calculation
        if ($this->is_schedule && $this->defaultSchedule) {
            $nextDelivery = now()
                ->addDays($this->defaultSchedule->number_of_days)
                ->format('Y-m-d');
        }
        $itemsCount = $this->items?->count() ?? 0;

        $categoryNames = $this->categories->pluck('name')->filter()->implode(' - ');

        return [
            'id' => $this->id,

            // naming for section
            'title' => $this->name,
            'desc'  => $this->description,

            // media
            'image' => $this->image_url,
            'images' => $this->image_urls,

            // category
            'category' => $categoryNames !== '' ? $categoryNames : $this->category?->name,

            // pricing
            'original_price' => round($this->calculated_price, 2),
            'discount_value' => $this->discount,
            'discount_type'  => $this->discount_type,
            'discount_amount' => round($this->discount_amount, 2),
            'price_after_discount' => round($this->final_price, 2),

            // stats
            'rating'   => (float) $this->average_rating,
            'num_sold' => (int) $this->num_sold,
            'saving'   => round($this->discount_amount, 2),

            // offer
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),

            // delivery
            'next_delivery_date' => $nextDelivery,
            'items_count' => $itemsCount,
            'delivery_price' => $this->delivery_price,
            'is_favorite' => (bool) ($this->is_favorite ?? false),

        ];
    }
    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }


    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        ->where("name->$locale", 'like', "%{$word}%");
                });
            }
        });
    }
}
