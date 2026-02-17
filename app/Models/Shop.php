<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Http\Resources\Shop\AllResource;

class Shop extends Model implements Sectionable
{
    use HasTranslations;

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
        'is_free_delivery'
    ];
    public function area()
    {
        return $this->belongsTo(Area::class);
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
    public function logo(): ?Media
    {
        return $this->media()->where('collection', 'logo')->first();
    }
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
        $mediaLogo = $this->media()->where('collection', 'logo')->first()?->path;
        return $mediaLogo ?? $this->logo;
    }


    public function getCoverImagesUrls(): array
    {
        // Try media relationship first
        $mediaImages = $this->media()
            ->where('collection', 'cover')
            ->orderBy('order')
            ->get()
            ->map(fn($media) => $media->path)
            ->toArray();

        // If no media images, use cover_images field
        if (empty($mediaImages) && !empty($this->cover_images)) {
            return is_array($this->cover_images) ? $this->cover_images : [];
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
}
