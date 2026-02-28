<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Coupon extends Model
{
    use HasTranslations, LogsActivity;
    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_value',
        'start_at',
        'end_at',
        'max_uses',
        'used_count',
        'city_id',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    // Helpers
    public function isExpired(): bool
    {
        return now()->gt($this->end_at);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && now()->between($this->start_at, $this->end_at)
            && $this->used_count < $this->max_uses;
    }

    // Morph relations
    public function products()
    {
        return $this->morphedByMany(Product::class, 'couponable');
    }

    public function categories()
    {
        return $this->morphedByMany(Category::class, 'couponable');
    }

    public function vendors()
    {
        return $this->morphedByMany(Vendor::class, 'couponable');
    }
}
