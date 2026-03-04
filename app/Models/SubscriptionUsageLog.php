<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsageLog extends Model
{
    protected $fillable = [
        'subscription_id',
        'order_id',
        'usage_type',
        'value',
        'remaining_orders_before',
        'remaining_orders_after',
        'remaining_free_deliveries_before',
        'remaining_free_deliveries_after',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'remaining_orders_before' => 'integer',
        'remaining_orders_after' => 'integer',
        'remaining_free_deliveries_before' => 'integer',
        'remaining_free_deliveries_after' => 'integer',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
