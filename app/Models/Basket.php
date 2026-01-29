<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;

class Basket extends Model implements Sectionable
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_id',
        'name',
        'num_varieties',
        'offer_ends_at',
        'price',
        'discount',
        'discount_type',
        'rating',
        'num_sold',
        'image',
        'is_schedule',
        'delivery_price'
    ];

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }
    public $translatable = ['name'];

    protected $casts = [
        'offer_ends_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
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
        return asset('storage/' . $this->image);
    }
    public function schedules()
    {
        return $this->hasMany(BasketSchedule::class);
    }

    public function activeSchedule(): HasMany
    {
        return $this->hasMany(BasketSchedule::class)->where('is_active', true);
    }
    public function toSectionArray(): array
    {
        $nextDelivery = null;

        if ($this->activeSchedule && $this->activeSchedule->count()) {
            $schedule = $this->activeSchedule->first();
            $nextDelivery = now()
                ->addDays($schedule->number_of_days)
                ->format('Y-m-d');
        }
        $itemsCount = $this->items?->count() ?? 0;

        return [
            'id' => $this->id,

            // naming for section
            'title' => $this->name,
            'desc'  => null,

            // media
            'image' => $this->image_url,

            // category
            'category' => $this->category?->name,

            // pricing
            'original_price' => round($this->calculated_price, 2),
            'discount_value' => $this->discount,
            'discount_type'  => $this->discount_type,
            'discount_amount' => round($this->discount_amount, 2),
            'price_after_discount' => round($this->final_price, 2),

            // stats
            'rating'   => (float) $this->rating,
            'num_sold' => (int) $this->num_sold,
            'saving'   => round($this->discount_amount, 2),

            // offer
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),

            // delivery
            'next_delivery_date' => $nextDelivery,

            // section ui
            'top_badges' => [],
            'bottom_badges' => [],
            'items_count' => $itemsCount,
            'delivery_price' => $this->delivery_price

        ];
    }

    // public function toSectionArray(): array
    // {
    //     return [
    //         'id'       => $this->id,
    //         'title'     => $this->name,
    //         'desc'     => null,
    //         'image'    => $this->image_url,
    //         'price' => $this->calculated_price,
    //         'price_after_discount' => $this->final_price,
    //         'discount' => $this->discount,
    //         'top_badges' => [],
    //         'bottom_badges' => [],
    //     ];
    // }
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }
}
