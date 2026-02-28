<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Gift extends Model
{
    use SoftDeletes, HasTranslations, LogsActivity;
    protected $fillable = [
        'name',
        'description',
        'image',
        'points_required',
        'stock_quantity',
        'is_active',
        'category_id',
        'shop_product_variant_id',
        'terms_conditions',
    ];
    public $translatable = ['name', 'description', 'terms_conditions'];
    protected $casts = [
        'is_active' => 'boolean',
        'points_required' => 'integer',
        'stock_quantity' => 'integer',
    ];

    /**
     * Relationship with ShopProductVariant
     */
    public function shopProductVariant()
    {
        return $this->belongsTo(\App\Models\ShopProductVariant::class, 'shop_product_variant_id');
    }

    /**
     * Get exchanges for this gift
     * Note: gift_id is stored in JSON exchange_data, so we can't use standard relationship
     */
    public function getExchangesAttribute()
    {
        return PointExchange::where('exchange_type', 'gift')
            ->whereRaw("JSON_EXTRACT(exchange_data, '$.gift_id') = ?", [$this->id])
            ->get();
    }

    /**
     * Get exchanges count for this gift
     */
    public function getExchangesCountAttribute(): int
    {
        return PointExchange::where('exchange_type', 'gift')
            ->whereRaw("JSON_EXTRACT(exchange_data, '$.gift_id') = ?", [$this->id])
            ->count();
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
