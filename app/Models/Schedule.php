<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use App\Http\Resources\Admin\Schedule\AllResource;

class Schedule extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'interval_days',
        'is_active',
        'discount_type',
        'discount_value',
    ];
    public array $translatable = [
        'name',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    // ========== Relations ==========


    public function userBaskets(): HasMany
    {
        return $this->hasMany(UserBasketSchedule::class, 'schedule_id');
    }

    // ========== Helper Methods ==========

 public function toSectionArray()
    {
        return AllResource::make($this);
    }
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
