<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        // 'user_id',
        'driver_id',
        'user_address_id',
        'is_instant_delivery',
        'order_status',
        'cart_type',
        'delivery_price',
        'total_quantity',
        'total',
        'coupon_discount',
    ];

    // العلاقة مع عناصر الطلب
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع السائق
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // العلاقة مع العنوان
    public function address()
    {
        return $this->belongsTo(UserAddress::class);
    }
}
