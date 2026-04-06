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

    // تحويل من الليرة السورية (العملة الأساسية) للعملة الحالية
    public function convertFromBase(?float $amountInSYP): ?float
    {
        if ($amountInSYP === null) {
            return null;
        }

        // إذا العملة هي الليرة السورية (الأساسية)، نرجع كما هو
        if ($this->is_default) {
            return round($amountInSYP, 2);
        }

        return round($amountInSYP * $this->exchange_rate, 2);
    }

    // تحويل من العملة الحالية للليرة السورية (العملة الأساسية)
    public function convertToBase(?float $amount): ?float
    {
        if ($amount === null) {
            return null;
        }

        if ($this->is_default) {
            return round($amount, 2);
        }

        return round($amount / $this->exchange_rate, 2);
    }

    // للتوافق مع الكود القديم - تحويل من الليرة السورية
    public function convertFromUSD(?float $amountInBase): ?float
    {
        return $this->convertFromBase($amountInBase);
    }

    // للتوافق مع الكود القديم - تحويل للليرة السورية
    public function convertToUSD(?float $amount): ?float
    {
        return $this->convertToBase($amount);
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
