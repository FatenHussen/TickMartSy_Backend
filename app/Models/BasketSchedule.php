<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class BasketSchedule extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'basket_id',
        'type',
        'title',
        'discount_type',
        'discount_value',
        'is_active',
    ];
    public $translatable = ['title'];
    protected $casts = [
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function getIntervalDaysAttribute(): int
    {
        return match ($this->type) {
            '3_days'   => 3,
            'weekly'   => 7,
            'biweekly' => 14,
            'monthly'  => 30,
        };
    }
}
