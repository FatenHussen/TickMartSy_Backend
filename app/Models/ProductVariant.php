<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'attributes_values_ids',
    ];

    protected $casts = [
        'attributes_values_ids' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shops()
    {
        return $this->hasMany(ShopProductVariant::class);
    }
    public function media()
    {
        return $this->morphMany(ProductMedia::class, 'mediable')
            ->where('collection', 'variant')
            ->orderBy('order');
    }
}
