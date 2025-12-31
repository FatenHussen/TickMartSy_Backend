<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PageSection extends Model
{
    use HasTranslations;
    public array $translatable = ['name'];

    protected $fillable = ['name', 'page_id', 'section_id', 'position', 'order', 'filters'];
    protected $casts = ['filters' => 'array'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
