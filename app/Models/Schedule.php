<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Translatable\HasTranslations;
use App\Http\Resources\UserBasketSchedule\ScheduleResource;

class Schedule extends Model implements Sectionable
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'image',
        'interval_days',
        'is_active',
        'discount_type',
        'discount_value',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function userBaskets(): HasMany
    {
        return $this->hasMany(UserBasketSchedule::class, 'schedule_id');
    }

    public function baskets(): HasMany
    {
        return $this->hasMany(Basket::class, 'schedule_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(ProductMedia::class, 'mediable');
    }

    public function scheduleImages(): MorphMany
    {
        return $this->media()
            ->where('collection', 'schedule')
            ->orderBy('order');
    }

    public function badges(): MorphToMany
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        $firstMedia = $this->relationLoaded('scheduleImages')
            ? $this->scheduleImages->first()
            : $this->scheduleImages()->first();

        return $firstMedia?->url;
    }

    public function getImageUrlsAttribute(): array
    {
        $urls = [];

        if ($this->image) {
            $urls[] = asset('storage/' . $this->image);
        }

        $mediaUrls = ($this->relationLoaded('scheduleImages')
            ? $this->scheduleImages
            : $this->scheduleImages()->get())
            ->map(fn ($media) => $media->url)
            ->values()
            ->all();

        return array_values(array_unique(array_merge($urls, $mediaUrls)));
    }

    public function toSectionArray(): array
    {
        if (!$this->relationLoaded('badges')) {
            $this->load('badges');
        }

        if (!$this->relationLoaded('scheduleImages')) {
            $this->load('scheduleImages');
        }

        return (new ScheduleResource($this))->resolve();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
