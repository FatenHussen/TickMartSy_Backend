<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'shop_product_variant_id',
        'product_name',           // snapshot
        'product_image',          // snapshot
        'variant_attributes',     // snapshot JSON
        'quantity',
        'price',                  // legacy alias of unit_price
        'unit_price',
        'final_price',
        'subtotal',
        'extras_total',
        'total',
        'item_status',
        'pending_at',
        'preparing_at',
        'out_delivery_at',
        'delivered_at',
        'driver_id',
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

    // العلاقة مع الـ extras
    public function extras()
    {
        return $this->hasMany(OrderItemExtra::class);
    }

    // لو حاب تحوّل variant_attributes من JSON تلقائياً
    protected $casts = [
        'variant_attributes' => 'array',
        'price' => 'float',
        'unit_price' => 'float',
        'final_price' => 'float',
        'subtotal' => 'float',
        'extras_total' => 'float',
        'total' => 'float',
    ];

    protected static function booted()
    {
        static::updating(function ($item) {
            if ($item->isDirty('item_status')) {
                $timestampsMap = [
                    OrderStatus::PENDING->value      => 'pending_at',
                    OrderStatus::PREPARING->value    => 'preparing_at',
                    OrderStatus::OUT_DELIVERY->value => 'out_delivery_at',
                    OrderStatus::DELIVERED->value    => 'delivered_at',
                ];

                $field = $timestampsMap[$item->item_status] ?? null;

                if ($field && is_null($item->$field)) {
                    $item->$field = now();
                }
            }
        });
    }
}
