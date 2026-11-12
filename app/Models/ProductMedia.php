<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    const COLLECTION_PRODUCT = 'product';
    const COLLECTION_VARIANT = 'variant';

    protected $fillable = [
        'collection',
        'path',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Polymorphic relation
     */
    public function mediable()
    {
        return $this->morphTo();
    }


    /**
     * Full URL
     */
    public function getUrlAttribute(): ?string
    {
        $path = trim((string) $this->path);

        if ($path === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function isUsable(): bool
    {
        return $this->is_active !== false && $this->url !== null;
    }
}
