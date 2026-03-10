<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use LogsActivity;
    protected $fillable = [
        'user_id',
        'driver_id',
        'user_address_id',
        'payment_method_id',
        'basket_id',
        'basket_schedule_id',
        'is_instant_delivery',
        'status',
        'cart_type',
        'delivery_price',
        'total_quantity',
        'total',
        'subtotal',
        'basket_discount',            // خصم السلة
        'coupon_discount',            // خصم الكوبون
        'coupon_discount_from_points', // خصم نقاط من الكوبون
        'free_delivery_from_points',  // توصيل مجاني من النقاط
        'coupon_id',
        'coupon_code',
        'subscription_discount',
        'subscription_free_delivery',
        'subscription_points_bonus',
        'promotion_id',
        'promotion_discount',
        'pause_at'

    ];
    protected $casts = [
        'is_instant_delivery' => 'boolean',
        'subscription_free_delivery' => 'boolean',
        'pause_at' => 'datetime',
    ];

    protected $appends = ['affiliate_commission'];

    //affiliate_commission
    protected function affiliateCommission(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->affiliate_id && $this->affiliate_rate
                ? round($this->total * ($this->affiliate_rate / 100), 2)
                : 0
        );
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }


    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionUsageLogs()
    {
        return $this->hasMany(SubscriptionUsageLog::class);
    }

    public function basketSchedule()
    {
        return $this->belongsTo(BasketSchedule::class);
    }

    public function usedCouponExchange()
    {
        return $this->belongsTo(PointExchange::class, 'used_coupon_exchange_id');
    }

    public function usedFreeDeliveryExchange()
    {
        return $this->belongsTo(PointExchange::class, 'used_free_delivery_exchange_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    protected static function booted()
    {
        static::created(function ($order) {

            $order->updateQuietly([
                'order_code' => 'ORD-' .
                    now()->format('ymd') . '-' .
                    strtoupper(Str::random(4)) .
                    $order->id
            ]);

            Log::info("Helllllo");
        });

        static::updating(function ($order) {
            if ($order->isDirty('status')) {
                // Store old status before update
                // $order->_oldStatus = $order->getOriginal('status');

                $timestampsMap = [
                    OrderStatus::PENDING->value      => 'pending_at',
                    OrderStatus::PREPARING->value    => 'preparing_at',
                    OrderStatus::OUT_DELIVERY->value => 'out_delivery_at',
                    OrderStatus::DELIVERED->value    => 'delivered_at',
                ];

                $field = $timestampsMap[$order->status] ?? null;

                if ($field && is_null($order->$field)) {
                    $order->$field = now();
                }
            }
        });
    }
}
