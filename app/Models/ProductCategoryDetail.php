<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ProductCategoryDetail extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_detail_id',
        'product_id',
        'detail_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public array $translatable = [
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

    public function categoryDetail()
    {
        return $this->belongsTo(CategoryDetail::class);
    }
}
