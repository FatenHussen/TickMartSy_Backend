<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class BasketSchedule extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'basket_id',
        'number_of_days',
        'title',
        'discount_type',
        'discount_value',
        'is_active',
        'is_default',
    ];
    public $translatable = ['title'];
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }
}
