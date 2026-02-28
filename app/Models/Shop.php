<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Http\Resources\Shop\AllResource;
use App\Traits\LogsActivity;

class Shop extends Model implements Sectionable
{
    use HasTranslations, LogsActivity;

    public array $translatable = ['name', 'description', 'address'];

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'mobile',
        'email',
        'lat',
        'lng',
        'area_id',
        'working_hours',
        'is_active',
        'ratings_count',
        'ratings_sum',
        'vendor_id',
        'is_default',
        'is_free_delivery',
        'logo'
    ];
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    protected $casts = [
        'working_hours'     => 'array',
        'cover_images'      => 'array',
        'is_active'         => 'boolean',
        'is_free_delivery'         => 'boolean',
        'ratings_count'     => 'integer',
        'ratings_sum'       => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ratings_count > 0
                ? round($this->ratings_sum / $this->ratings_count, 2)
                : 0.00
        );
    }

    public function media()
    {
        return $this->morphMany(VendorMedia::class, 'mediable');
    }

    public function coverImages()
    {
        return $this->media()->where('collection', 'cover')
            ->orderBy('order');
    }
    // public function logo(): ?Media
    // {
    //     return $this->media()->where('collection', 'logo')->first();
    // }
    public function  getLogoUrlAttribute()
    {
        return asset('storage/' . $this->logo);
    }

    public function isOpenNow(): bool
    {
        $day = strtolower(now()->englishDayOfWeek); // monday, tuesday, ...
        $hours = $this->working_hours[$day] ?? null;

        if (!$hours || ($hours['closed'] ?? false)) {
            return false;
        }

        $now = now()->format('H:i');
        return $now >= $hours['open'] && $now <= $hours['close'];
    }



    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

    // public function orders(): HasMany
    // {
    //     return $this->hasMany(Order::class);
    // }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function getLogoUrl(): ?string
    {
        // Try media relationship first, fallback to logo field
        // $mediaLogo = $this->media()->where('collection', 'logo')->first()?->path;
        // return $mediaLogo ?? $this->logo;
        return asset('storage/' . $this->logo);
    }


    public function getCoverImagesUrls(): array
    {
        // Try media relationship first
        $mediaImages = $this->media()
            ->where('collection', 'cover')
            ->orderBy('order')
            ->get()
            ->map(fn($media) => asset('storage/' . $media->path))
            ->toArray();

        // If no media images, use cover_images field
        if (empty($mediaImages) && !empty($this->cover_images)) {
            $images = is_array($this->cover_images) ? $this->cover_images : [];
            return array_map(fn($path) => asset('storage/' . $path), $images);
        }

        return $mediaImages;
    }

    public function getMediaUrls(string $collection): array
    {
        return $this->media()
            ->where('collection', $collection)
            ->orderBy('order')
            ->get()
            ->map(fn($media) => $media->path)
            ->toArray();
    }

    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class, 'shop_id');
    }

    public function activeSubscription(): ?VendorSubscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>=', now()->toDateString())
            ->orderByDesc('ends_at')
            ->first();
    }

    public function currentPackage(): ?VendorPackage
    {
        $sub = $this->activeSubscription();
        return $sub?->package;
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'shop_service');
    }
    public function productVariants()
    {
        return $this->hasMany(ShopProductVariant::class);
    }
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function toSectionArray()
    {
        return AllResource::make($this);
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        // Shop basic fields
                        ->where("name->$locale", 'like', "%{$word}%")
                        ->orWhere("description->$locale", 'like', "%{$word}%")
                        ->orWhere("address->$locale", 'like', "%{$word}%")

                        // Vendor name
                        ->orWhereHas('vendor', function ($vendorQuery) use ($word, $locale) {
                            $vendorQuery->where("name->$locale", 'like', "%{$word}%");
                        });
                });
            }
        })
            ->where('is_active', true)
            ->withCount(['productVariants'])
            ->orderByDesc('product_variants_count');
    }

    public function getImageUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : null;
    }
}
