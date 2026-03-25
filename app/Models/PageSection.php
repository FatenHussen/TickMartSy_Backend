<?php

namespace App\Models;

use App\Services\Base\Section\SectionApiService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PageSection extends Model
{
    use HasTranslations, LogsActivity;
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'page_id',
        'section_id',
        'position',
        'order',
        'filters',
        'background_card_color',
        'background_color',
        'display_type_id'
    ];
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
