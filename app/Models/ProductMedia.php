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
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
