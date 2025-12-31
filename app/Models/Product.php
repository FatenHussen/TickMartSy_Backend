<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country',
        'model',
        'price',
        'price_after_discount',
        'quantity',
        'barcode',
        'time_prepare',
        'bought_with',
    ];

    public array $translatable = [
        'name',
        'description',
        'full_description',
        'country',
    ];

    protected $casts = [
        'bought_with' => 'array',
        'time_prepare' => 'datetime:H:i',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categoryDetails()
    {
        return $this->hasMany(ProductCategoryDetail::class);
    }

    public function extraDetails()
    {
        return $this->hasMany(ProductExtraDetail::class);
    }
}
