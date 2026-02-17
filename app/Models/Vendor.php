<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Translatable\HasTranslations;

class Vendor extends Model
{
    use HasTranslations;
    protected $imageFolder = "vendors";
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'owner_name',
        'owner_phone',
        'commercial_register',
        'contract_date',
        'contract_number',
        'contract_duration_months',
        'commission_rate',
        'is_active',
        'ratings_count',
        'ratings_sum',
    ];

    protected $casts = [
        'contract_date'     => 'date',
        'commission_rate'   => 'decimal:2',
        'is_active'         => 'boolean',
        'ratings_count'     => 'integer',
        'ratings_sum'       => 'integer',
    ];


    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }
    public function users()
    {
        return $this->belongsToMany(
            \App\Models\VendorUser::class,
            'shop_users',
            'shop_id',
            'vendor_user_id'
        );
    }


    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ratings_count > 0
                ? round($this->ratings_sum / $this->ratings_count, 2)
                : 0.00
        );
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
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
}
