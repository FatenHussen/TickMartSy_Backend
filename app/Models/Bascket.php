<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'num_varieties',
        'num_components',
        'offer_ends_at',
        'price',
        'discount',
        'discount_type',
        'savings',
        'rating',
        'num_sold',
    ];

    protected $casts = [
        'images' => 'array',
        'offer_ends_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function items()
    {
        return $this->hasMany(BasketItem::class);
    }

    public function getCalculatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute()
    {
        $total = $this->calculated_price; 

        if ($this->discount_type === 'percentage') {
            return $total * ($this->discount / 100);
        } else {
            return $this->discount;
        }
    }

    public function getFinalPriceAttribute()
    {
        return $this->calculated_price - $this->discount_amount;
    }

    protected static function booted()
    {
        static::saving(function ($basket) {
            $basket->savings = $basket->discount_amount;
            $basket->price = $basket->calculated_price;
        });
    }
}
