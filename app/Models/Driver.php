<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, LogsActivity, SoftDeletes;
    protected $appends = [
        'average_rating',
        'total_earnings',
        'total_orders',
        'total_delivered',
        'today_earnings',
        'today_delivered',
        'average_delivery_time',
        'cancellation_rate',
    ];
    protected $fillable = [
        'phone',
        'email',
        'password',
        'name',
        'is_active',
        'address',
        'status',
        'rate_per_order',
        'image',
        'vehicle_type',
        'vehicle_name',
        'vehicle_number',
        'vehicle_image',
    ];
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function completedOrders()
    {
        return $this->hasMany(Order::class)->where('status', OrderStatus::DELIVERED->value);
    }
    public function fcmTokens()
    {
        return $this->morphMany(UserToken::class, 'tokenable');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getVehicleImageUrlAttribute()
    {
        return $this->vehicle_image ? asset('storage/' . $this->vehicle_image) : null;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getTotalEarningsAttribute(): float
    {
        $sum = $this->completedOrders()->sum('delivery_price');

        return $this->calculateDriverEarnings((float) $sum);
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getTotalDeliveredAttribute(): int
    {
        return $this->completedOrders()->count();
    }

    public function getTodayEarningsAttribute(): float
    {
        $sum = $this->completedOrders()
            ->whereDate('delivered_at', now()->toDateString())
            ->sum('delivery_price');

        return $this->calculateDriverEarnings((float) $sum);
    }

    public function getTodayDeliveredAttribute(): int
    {
        return $this->completedOrders()
            ->whereDate('delivered_at', now()->toDateString())
            ->count();
    }

    public function getAverageDeliveryTimeAttribute(): float
    {
        $seconds = $this->completedOrders()
            ->whereNotNull('out_delivery_at')
            ->whereNotNull('delivered_at')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, out_delivery_at, delivered_at)'));

        return $seconds ? round($seconds / 60, 2) : 0.0;
    }

    public function getCancellationRateAttribute(): float
    {
        $total = $this->orders()->count();

        if ($total === 0) {
            return 0.0;
        }

        $statuses = [
            OrderStatus::CANCELLED->value,
            OrderStatus::REJECTEDBYDELIVERY->value,
            OrderStatus::FAILDDELIVER->value,
        ];

        $canceled = $this->orders()->whereIn('status', $statuses)->count();

        return round(($canceled / $total) * 100, 1);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_driver');
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'driver_shop');
    }

    private function earningsMultiplier(): float
    {
        $rate = (float) $this->rate_per_order;

        return $rate > 0 ? $rate / 100 : 0.0;
    }

    private function calculateDriverEarnings(float $deliverySum): float
    {
        return round($deliverySum * $this->earningsMultiplier(), 2);
    }
}
