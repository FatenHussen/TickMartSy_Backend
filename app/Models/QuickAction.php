<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class QuickAction extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'button_text',
        'page_id',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'button_text' => 'array',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public array $translatable = ['title', 'button_text'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}
