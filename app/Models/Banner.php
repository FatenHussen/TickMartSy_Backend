<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Jobs\DeleteBannerJob;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use HasTranslations, LogsActivity;
    public array $translatable = ['title', 'description', 'button_text'];
    protected $fillable = [
        'title',
        'description',
        'button_text',
        'image',
        'link',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::saved(function (Banner $banner) {
            $banner->scheduleDeletionJob();
        });
    }

    public function scheduleDeletionJob(): void
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return;
        }

        DeleteBannerJob::dispatch($this->id)->delay($this->expires_at);
    }

    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'banner_page_section')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->title,
            'desc'     => $this->description,
            'button_text' => $this->button_text,
            'image'    => $this->image_url,
            'price' => null,
            'discount' => null,
            // 'top_badges' => [],
            // 'bottom_badges' => [],
        ];
    }
}
