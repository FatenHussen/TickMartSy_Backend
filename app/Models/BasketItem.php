<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BasketItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'basket_id',
        'product_id',
        'variant_id',
        'quantity',
        'is_required',
        'min_quantity',
        'max_quantity',
        'price',
        'shop_product_variant_id',
        'is_extra',
        'shop_product_variant_ids'

    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_extra' => 'boolean',
        'price'       => 'decimal:2',
        'shop_product_variant_ids' => 'array',

    ];


    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function canAdjustQuantity(): bool
    {
        if (!$this->is_required) {
            return true;
        }

        return $this->quantity < $this->max_quantity;
    }
    public function getSubtotalAttribute(): float
    {
        return round($this->quantity * $this->price, 2);
    }
    public function shopProductVariant()
    {
        return $this->belongsTo(ShopProductVariant::class);
    }
    public function shopVariants()
    {
        if (!$this->shop_product_variant_ids) {
            return collect();
        }

        return \App\Models\ShopProductVariant::query()
            ->whereIn('id', $this->shop_product_variant_ids)
            ->with([
                'product.brand',  
                'product.media',   
            ])
            ->get();
    }
    public function canDelete()
    {
        return $this->basket->is_scheduled;
    }

    public function canHaveExtras()
    {
        return $this->basket->is_scheduled;
    }
}
