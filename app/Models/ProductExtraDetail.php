<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductExtraDetail extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'product_id',
        'detail_key',
        'detail_value',
        'price',
    ];

    public array $translatable = [
        'detail_key',
        'detail_value',
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
}
