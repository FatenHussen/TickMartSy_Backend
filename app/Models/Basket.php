<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

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
        'is_schedule'
    ];
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
        return [
            'id'       => $this->id,
            'title'     => $this->name,
            'desc'     => null,
            'image'    => $this->image_url,
            'price' => $this->price,
            'price_after_discount' => $this->final_price,
            'discount' => $this->discount,
            'top_badges' => [],
            'bottom_badges' => [],
        ];
    }
}
