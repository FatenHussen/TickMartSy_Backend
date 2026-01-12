<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketItemCompany extends Model
{
    use HasFactory;

    protected $table = 'basket_item_companies';

    protected $fillable = [
        'basket_item_id',
        'company',
        'is_default',
        'company_specific_price',
    ];

    protected $casts = [
        'company'                 => 'array',
        'is_default'              => 'boolean',
        'company_specific_price'  => 'decimal:2',
    ];


    public function basketItem(): BelongsTo
    {
        return $this->belongsTo(BasketItem::class, 'basket_item_id');
    }


    public function getCompanyNameAttribute(): ?string
    {
        return $this->company['name'] ?? null;
    }

    public function getCompanyIdAttribute()
    {
        return $this->company['id'] ?? null;
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