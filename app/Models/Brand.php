<?php

namespace App\Models;

use App\Http\Resources\Admin\Brand\AllResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Brand extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;
    protected $fillable = ['name', 'image'];
    public $translatable = ['name'];

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function toSectionArray()
    {
        // return [
        //     'id'       => $this->id,
        //     'title'     => $this->name,
        //     'desc'     => null,
        //     'image'    => $this->image_url,
        //     'price' => null,
        //     'price_after_discount' => null,
        //     'discount' => null,
        //     'top_badges' => [],
        //     'bottom_badges' => [],
        // ];
        return AllResource::make($this);
    }


    public function products()
    {
        return $this->hasMany(Product::class);
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
    public function vendors()
    {
        return $this->hasManyThrough(
            Vendor::class,
            Product::class,
            'brand_id',
            'id',
            'id',
            'vendor_id'
        );
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        // Brand name
                        ->where("name->$locale", 'like', "%{$word}%");
                });
            }
        })
            ->withCount(['products' => function ($q) {
                $q->where('approval_status', 'approved');
            }])
            ->orderByDesc('products_count');
    }
}
