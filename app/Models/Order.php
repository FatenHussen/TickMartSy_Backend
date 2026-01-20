<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{

    protected $fillable = [
        'user_id',
        'driver_id',
        'user_address_id',
        'is_instant_delivery',
        'order_status',
        'cart_type',
        'delivery_price',
        'total_quantity',
        'total',
        'subtotal',
        'basket_discount',
        'coupon_discount',
        'discount_source',
        'coupon_discount',
    ];

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
