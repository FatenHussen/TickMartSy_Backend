<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class VendorPackage extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'is_active',
        'max_products',
        'is_featured',
        'has_premium_badge',
        'search_priority',
        'max_campaigns',
        'has_banner_ad',
        'has_sales_reports',
        'has_analytics',
        'report_level',
        'order_priority',
        'can_set_prep_time',
        'custom_shipping_options',
        'has_vendor_delivery',
        'commission_rate',
        'commission_per_order',
        'activation_fee_waived',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_premium_badge' => 'boolean',
        'has_banner_ad' => 'boolean',
        'has_sales_reports' => 'boolean',
        'has_analytics' => 'boolean',
        'can_set_prep_time' => 'boolean',
        'custom_shipping_options' => 'boolean',
        'has_vendor_delivery' => 'boolean',
        'activation_fee_waived' => 'boolean',
        'commission_rate' => 'decimal:2',
        'commission_per_order' => 'decimal:2',
    ];

    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
