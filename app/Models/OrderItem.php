<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    // use SoftDeletes;


    protected $fillable = [
        'order_id',
        'shop_product_variant_id',
        'product_name',           // snapshot
        'variant_attributes',     // snapshot JSON
        'quantity',
        'price',
        'discount',
        'item_status',
    ];

    // العلاقة مع الـ order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // العلاقة مع الـ ShopProductVariant
    public function shopProductVariant()
    {
        return $this->belongsTo(ShopProductVariant::class);
    }

    // لو حاب تحوّل variant_attributes من JSON تلقائياً
    protected $casts = [
        'variant_attributes' => 'array',
    ];
}
