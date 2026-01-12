<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{

    protected $fillable = [
        'name',
        'description',
        'image',
        'video_url',
        'discount',
        'rating',
        'orders_count',
        'top_badge_id',
        'bottom_badge_id',
        'is_active'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];


    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
