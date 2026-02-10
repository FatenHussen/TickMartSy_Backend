<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecipeItem extends Model
{
    use HasFactory;

    protected $table = 'recipe_items';

    protected $fillable = [
        'recipe_id',
        'shop_product_variant_id',
        'switchable_category_id',
        'quantity',
        'is_required',
        'min_quantity',
        'max_quantity',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
    ];

    /* =======================
     * Relationships
     * ======================= */

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function shopProductVariant()
    {
        return $this->belongsTo(ShopProductVariant::class);
    }

    public function switchableCategory()
    {
        return $this->belongsTo(Category::class, 'switchable_category_id');
    }
}
