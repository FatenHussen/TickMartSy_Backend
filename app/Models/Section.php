<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasTranslations;
    public array $translatable = ['name'];

    protected $fillable = ['name', 'type', 'api_method', 'filters'];
    protected $casts = ['filters' => 'array'];

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_sections');
    }

    public function sectionItems()
    {
        return $this->hasMany(SectionItem::class)->orderBy('order');
    }
}
