<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'link',
        'order',
        'active',
    ];

    // علاقة Banner مع PageSection (Pivot Table)
    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'banner_page_section')
            ->withPivot('order')
            ->withTimestamps();
    }
}
