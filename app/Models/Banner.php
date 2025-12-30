<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public array $translatable = ['title', 'description'];
    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        // 'order',
        // 'is_active',
    ];

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
}
