<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ------------------ Scopes ------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /* ------------------ Static Methods ------------------ */

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->where('is_active', true)->first();
            
            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $type = 'string'): bool
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'is_active' => true,
            ]
        );

        // Clear cache
        Cache::forget("system_setting_{$key}");
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $cacheKey = "system_settings_group_{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return static::byGroup($group)->active()->get()->mapWithKeys(function ($setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })->toArray();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget("system_setting_{$key}");
        }
        
        // Clear group caches
        $groups = static::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("system_settings_group_{$group}");
        }
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number', 'integer' => is_numeric($value) ? (float) $value : 0,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /* ------------------ Model Events ------------------ */

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
            Cache::forget("system_settings_group_{$setting->group}");
        });

        static::deleted(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
            Cache::forget("system_settings_group_{$setting->group}");
        });
    }
}