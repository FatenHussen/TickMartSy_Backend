<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ProductExtraDetail extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'product_id',
        'detail_key',
        'detail_value',
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
