<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'shop_id',
        'quantity',
        'price',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
