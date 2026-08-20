<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayType extends Model
{
    protected $fillable = ['manual_model', 'image', 'fields', 'allowed_page_slugs'];

    protected $casts = [
        'fields' => 'array',
        'allowed_page_slugs' => 'array',
    ];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
