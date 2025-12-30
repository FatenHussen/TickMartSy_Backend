<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorMedia extends Model
{
    protected $fillable = [
        'collection',
        'path',
        'order',
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
