<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMedia extends Model
{
    use SoftDeletes;
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
