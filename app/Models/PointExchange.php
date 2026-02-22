<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointExchange extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'exchange_type',
        'exchange_data',
        'status',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'exchange_data' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class);
    }

    /**
     * Get the gift for gift-type exchanges
     */
    public function getGiftAttribute()
    {
        if ($this->exchange_type === 'gift' && isset($this->exchange_data['gift_id'])) {
            return Gift::find($this->exchange_data['gift_id']);
        }
        return null;
    }

    /**
     * Get exchange details based on type
     */
    public function getExchangeDetailsAttribute(): array
    {
        return match ($this->exchange_type) {
            'coupon' => [
                'type' => 'كوبون خصم',
                'coupon_id' => $this->exchange_data['coupon_id'] ?? null,
                'discount_amount' => $this->exchange_data['discount_amount'] ?? null,
            ],
            'free_delivery' => [
                'type' => 'توصيل مجاني',
                'delivery_zones' => $this->exchange_data['delivery_zones'] ?? [],
                'expires_at' => $this->exchange_data['expires_at'] ?? null,
            ],
            'gift' => [
                'type' => 'هدية',
                'gift_id' => $this->exchange_data['gift_id'] ?? null,
                'gift_name' => $this->exchange_data['gift_name'] ?? null,
                'delivery_address' => $this->exchange_data['delivery_address'] ?? null,
            ],
            default => ['type' => 'غير محدد'],
        };
    }
}