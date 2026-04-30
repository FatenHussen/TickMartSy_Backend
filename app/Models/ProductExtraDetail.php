<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ProductExtraDetail extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_id',
        'detail_key',
        'detail_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_extra_detail_options')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
