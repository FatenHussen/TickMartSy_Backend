<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Order extends Model
{

    protected $fillable = [
        'user_id',
        'driver_id',
        'user_address_id',
        'is_instant_delivery',
        'status',
        'cart_type',
        'delivery_price',
        'total_quantity',
        'total',
        'subtotal',
        'basket_discount',
        'cart_type',
        'coupon_discount',
        'order_code',
        //markter
        'affiliate_id',
        'affiliate_rate',
        'affiliate_source',
        //timestamps
        'pending_at',
        'preparing_at',
        'out_delivery_at',
        'delivered_at',
        //driver
        'driver_id',
        'assigned_by'
    ];

    protected $casts = [
        'is_instant_delivery' => 'boolean',
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

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class);
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
