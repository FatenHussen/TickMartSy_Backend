<?php

namespace App\Models;

use App\Services\Base\Section\SectionApiService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasTranslations;
    public array $translatable = ['name'];
    protected $casts = [
        'filters' => 'array',
        'see_more_params' => 'array',
    ];


    protected $fillable = [
        'name',
        'type',
        'api_method',
        'filters',
        'manual_model',
        'see_more',
        'see_more_slug',
        'details_slug'
    ];

    public function apiData(array $filters = [])
    {
        return app(SectionApiService::class)->preview($this, $filters);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_sections');
    }

    public function sectionItems()
    {
        return $this->hasMany(SectionItem::class)->orderBy('order');
    }
}
