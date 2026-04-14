<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Badge extends Model
{
    use HasTranslations, LogsActivity;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'color',
        'position',
        'is_active',
        'type',
        'image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
