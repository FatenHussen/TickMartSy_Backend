<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'delivery_code',
        //markter
        'affiliate_id',
        'affiliate_rate',
        'affiliate_source'

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
    protected static function booted()
    {
        static::creating(function ($order) {
            $order->delivery_code = strtoupper(Str::random(6)); // 6 أحرف كبيرة عشوائية
        });
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
}
