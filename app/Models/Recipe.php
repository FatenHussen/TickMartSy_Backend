<?php

namespace App\Models;

use App\Http\Resources\Recipe\AllResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Recipe extends Model implements Sectionable
{
    use HasTranslations;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'description',
        'image',
        'video_url',
        'discount',
        'rating',
        'orders_count',
        'is_active',
        'delivery_price',
        'serves',
        'prepare_time'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];


    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function steps()
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }
    public function getTotalItemsPrice(): float
    {
        return round($this->items->sum(function ($item) {
            return $item->shopProductVariant->price * $item->quantity;
        }), 2);
    }

    public function getTotalAfterDiscount(): float
    {
        $total_before_discount = $this->getTotalItemsPrice();
        $discount_percentage = $this->discount ?? 0;

        $total_after_discount = $total_before_discount * (1 - $discount_percentage / 100);

        return round($total_after_discount, 2);
    }

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
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
    public function toSectionArray()
    {
        // return [
        //     'id'       => $this->id,
        //     'title'     => $this->title,
        //     'desc'     => $this->description,
        //     'image'    => $this->image_url,
        //     'price' => $this->getTotalItemsPrice(),
        //     'price_after_discount' => $this->getTotalAfterDiscount(),
        //     'discount' => $this->discount,
        //     'top_badges' => [],
        //     'bottom_badges' => [],
        // ];
        return AllResource::make($this);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }
}
