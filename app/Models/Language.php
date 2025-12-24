<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'languages';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'is_active',
        'is_default',
        'order',
        'flag_icon',
        'locale',
        'timezone',
        'date_format',
        'time_format',
        'decimal_separator',
        'thousands_separator',
        'currency_code',
        'currency_symbol',
        'show_in_menu',
        'show_in_switcher',
        'og_locale',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_switcher' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope a query to only include active languages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include languages visible in menu.
     */
    public function scopeMenuVisible($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope a query to only include languages visible in switcher.
     */
    public function scopeSwitcherVisible($query)
    {
        return $query->where('show_in_switcher', true);
    }

    /**
     * Scope a query to order languages by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Get the default language.
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Get active locales as array.
     */
    public static function getActiveLocales(): array
    {
        return cache()->remember('active_locales', 3600, function () {
            return static::active()
                ->ordered()
                ->pluck('code')
                ->toArray();
        });
    }

    /**
     * Get all languages with formatted data.
     */
    public static function getAllFormatted()
    {
        return cache()->remember('languages_formatted', 3600, function () {
            return static::ordered()
                ->get()
                ->map(function ($language) {
                    return [
                        'id' => $language->id,
                        'code' => $language->code,
                        'name' => $language->name,
                        'native_name' => $language->native_name,
                        'direction' => $language->direction,
                        'flag_icon' => $language->flag_icon,
                        'is_active' => $language->is_active,
                        'is_default' => $language->is_default,
                        'locale' => $language->locale,
                        'timezone' => $language->timezone,
                        'currency_symbol' => $language->currency_symbol,
                    ];
                });
        });
    }

    /**
     * Check if language is RTL.
     */
    public function isRTL(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Get flag image URL.
     */
    public function getFlagUrlAttribute()
    {
        if ($this->flag_icon) {
            if (str_starts_with($this->flag_icon, 'http')) {
                return $this->flag_icon;
            }
            return asset('storage/flags/' . $this->flag_icon);
        }

        // Flag emoji as fallback
        $flagEmojis = [
            'ar' => '🇸🇦',
            'en' => '🇺🇸',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'de' => '🇩🇪',
            'tr' => '🇹🇷',
            'ru' => '🇷🇺',
            'cn' => '🇨🇳',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
        ];

        return $flagEmojis[$this->code] ?? '🏴';
    }

    /**
     * Get locale with fallback.
     */
    public function getLocaleWithFallbackAttribute()
    {
        return $this->locale ?: $this->code . '_' . strtoupper($this->code);
    }

    /**
     * Format date according to language settings.
     */
    public function formatDate($date)
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        return $date->format($this->date_format);
    }

    /**
     * Format time according to language settings.
     */
    public function formatTime($time)
    {
        if (!$time instanceof \DateTime) {
            $time = new \DateTime($time);
        }

        return $time->format($this->time_format);
    }

    /**
     * Format number according to language settings.
     */
    public function formatNumber($number, $decimals = 2)
    {
        return number_format(
            $number,
            $decimals,
            $this->decimal_separator,
            $this->thousands_separator
        );
    }

    /**
     * Format currency according to language settings.
     */
    public function formatCurrency($amount, $decimals = 2)
    {
        $formatted = $this->formatNumber($amount, $decimals);

        if ($this->direction === 'rtl') {
            return $this->currency_symbol . ' ' . $formatted;
        }

        return $formatted . ' ' . $this->currency_symbol;
    }
}
