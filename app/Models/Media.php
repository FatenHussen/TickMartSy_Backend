<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'collection',
        'file_name',
        'path',
        'file_type',
        'order',
        'is_active',
    ];

    /**
     * Polymorphic relation
     */
    public function mediable(): MorphTo
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
