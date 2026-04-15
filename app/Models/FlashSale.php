<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'end_date',
        'is_active',
        'discount',
        'discount_type',
    ];

    protected $casts = [
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'discount' => 'float',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('end_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
            ->where('end_date', '<=', now());
    }
}
