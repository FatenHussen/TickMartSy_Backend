<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = [
        'code',
        'native_name',
        'direction',
        'is_active',
        'is_default',
        'order',
        'flag_icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    /* ------------------ Scopes ------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /* ------------------ Helpers ------------------ */

    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    public static function getActiveLocales(): array
    {
        return cache()->remember('active_locales', 3600, function () {
            return static::active()->ordered()->pluck('code')->toArray();
        });
    }

    public static function getAllFormatted()
    {
        return cache()->remember('languages_formatted', 3600, function () {
            return static::ordered()->get()->map(function ($language) {
                return [
                    'id' => $language->id,
                    'code' => $language->code,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'flag_icon' => $language->flag_icon,
                    'is_active' => $language->is_active,
                    'is_default' => $language->is_default,
                ];
            });
        });
    }

    public function isRTL(): bool
    {
        return $this->direction === 'rtl';
    }

    public function getFlagUrlAttribute(): string
    {
        if ($this->flag_icon) {
            return str_starts_with($this->flag_icon, 'http')
                ? $this->flag_icon
                : asset('storage/flags/' . $this->flag_icon);
        }

        $flagEmojis = [
            'ar' => '🇸🇦',
            'en' => '🇺🇸',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'de' => '🇩🇪',
            'tr' => '🇹🇷',
            'ru' => '🇷🇺',
            'zh' => '🇨🇳',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
        ];

        return $flagEmojis[$this->code] ?? '🏴';
    }
}
