<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BasketItemCompany extends Model
{
    use HasFactory;

    protected $table = 'basket_item_companies';

    protected $fillable = [
        'basket_item_id',
        'brand_id',
        'is_default',
        'company_specific_price',
    ];
    protected $casts = [
        'is_default'              => 'boolean',
        'company_specific_price'  => 'decimal:2',
    ];


    public function basketItem(): BelongsTo
    {
        return $this->belongsTo(BasketItem::class, 'basket_item_id');
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    public function hasCustomPrice(): bool
    {
        return $this->company_specific_price !== null;
    }

    public function getEffectivePriceAttribute(): ?float
    {
        return $this->company_specific_price ?? null;
    }
}