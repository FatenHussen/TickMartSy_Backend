<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Store extends Authenticatable
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name', 'description', 'address'];

    protected $fillable = [
        'name',
        'owner_name',
        'owner_phone',
        'description',
        'address',
        'phone',
        'mobile',
        'email',
        'commercial_register',
        'contract_date',
        'contract_number',
        'contract_duration_months',
        'commission_rate',
        'working_hours',
        'is_active',
        'ratings_count',
        'ratings_sum',
    ];


    protected $casts = [
        'working_hours'     => 'array',
        'cover_images'      => 'array',
        'contract_date'     => 'date',
        'commission_rate'   => 'decimal:2',
        'is_active'         => 'boolean',
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
            get: fn () => $this->ratings_count > 0
                ? round($this->ratings_sum / $this->ratings_count, 2)
                : 0.00
        );
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function coverImages(): MorphMany
    {
        return $this->media()->where('collection', 'cover')
            ->orderBy('order');
    }
    public function logo(): ?Media
    {
        return $this->media()->where('collection', 'logo')->first();
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


    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'store_area');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'store_service');
    }


    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'store_category');
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

}
