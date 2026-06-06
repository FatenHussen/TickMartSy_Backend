<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemExtra extends Model
{
    protected $fillable = [
        'order_item_id',
        'product_extra_detail_id',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function extraDetail(): BelongsTo
    {
        return $this->belongsTo(ProductExtraDetail::class, 'product_extra_detail_id');
    }
}
