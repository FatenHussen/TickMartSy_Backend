<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasTranslations, LogsActivity;
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
    ];
    public $translatable = ['name'];
    protected $casts = [
        'name' => 'array',
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->name[$locale] ?? $this->name['en'] ?? $this->code;
    }

    // تحويل من دولار للعملة الحالية
    public function convertFromUSD(float $amountInUSD): float
    {
        return round($amountInUSD * $this->exchange_rate, 2);
    }

    // تحويل من العملة الحالية للدولار
    public function convertToUSD(float $amount): float
    {
        return round($amount / $this->exchange_rate, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
