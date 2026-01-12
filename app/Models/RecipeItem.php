<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function shopProductVariant()
    {
        return $this->belongsTo(ShopProductVariant::class);
    }
}
