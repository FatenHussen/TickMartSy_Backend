<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use HasTranslations;
    public array $translatable = ['title', 'description'];
    protected $fillable = [
        'title',
        'description',
        'image',
        'link',

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
    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->title,
            'desc'     => $this->description,
            'image'    => $this->image_url,
            'price' => null,
            'discount' => null,
            'top_badges' => [],
            'bottom_badges' => [],
        ];
    }
}
