<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'shop_product_variant_id',
        'quantity',
        'note',
        'extras',
        'cart_type',
        'recipe_id',
        'admin_basket_id',
        'admin_schedule_basket_id',
        'basket_schedule_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'extras' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // withTrashed: a deleted offer must still surface as an unavailable row
    // rather than vanishing silently from the synced cart.
    public function shopProductVariant(): BelongsTo
    {
        return $this->belongsTo(ShopProductVariant::class)->withTrashed();
    }
}
