<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'status',
        'remaining_orders',
        'remaining_free_deliveries',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }

    public function usageLogs()
    {
        return $this->hasMany(SubscriptionUsageLog::class);
    }

    public function hasRemainingOrders()
    {
        return is_null($this->remaining_orders) || $this->remaining_orders > 0;
    }

    public function hasRemainingFreeDeliveries()
    {
        return $this->remaining_free_deliveries > 0;
    }
}
