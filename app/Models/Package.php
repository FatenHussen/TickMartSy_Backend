<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Package extends Model
{
    use HasTranslations;
    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'monthly_orders_limit',
        'free_delivery_count',
        'discount_percentage',
        'points_bonus',
        'is_active'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
