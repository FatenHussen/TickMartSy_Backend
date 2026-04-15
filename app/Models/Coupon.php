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
        'governorate_id',
        'city_id',
        'user_id',
        'is_active',
        'affiliate_id'
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

    public function scopeValid($query)
    {

        return $query->where('is_active', 1)
            // ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereColumn('used_count', '<', 'max_uses');
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

    public function shops()
    {
        return $this->morphedByMany(Shop::class, 'couponable');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        switch ($this->discount_type) {
            case 'percentage':
                return round($amount * ($this->discount_value / 100), 2);

            case 'fixed':
                return round(min($this->discount_value, $amount), 2);

            default:
                return 0;
        }
    }

    public function markter()
    {
        return $this->belongsTo(User::class, 'affiliate_id', 'affiliate_id');
    }
}
