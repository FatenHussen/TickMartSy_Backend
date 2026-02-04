<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'points_required',
        'stock_quantity',
        'is_active',
        'category_id',
        'terms_conditions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points_required' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function exchanges(): HasMany
    {
        return $this->hasMany(PointExchange::class);
    }

    /**
     * Check if gift is available for exchange
     */
    public function isAvailable(): bool
    {
        return $this->is_active 
            && ($this->stock_quantity === null || $this->stock_quantity > 0);
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $quantity = 1): bool
    {
        if ($this->stock_quantity === null) {
            return true; // Unlimited stock
        }

        if ($this->stock_quantity < $quantity) {
            return false; // Insufficient stock
        }

        $this->decrement('stock_quantity', $quantity);
        return true;
    }

    /**
     * Scope for available gifts
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stock_quantity')
                  ->orWhere('stock_quantity', '>', 0);
            });
    }
}