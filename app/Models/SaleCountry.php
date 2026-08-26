<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SaleCountry extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'icon',
        'is_active',
    ];

    public array $translatable = ['name'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sale_country_id');
    }

    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon) {
            return null;
        }

        // Emoji flag or absolute URL from seeder — return as-is
        if (
            str_starts_with($this->icon, 'http://')
            || str_starts_with($this->icon, 'https://')
            || ! preg_match('/[\\\\\\/]/', $this->icon)
        ) {
            return $this->icon;
        }

        return asset('storage/' . ltrim($this->icon, '/'));
    }
}
