<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopVendorService extends Model
{
    protected $table = 'shop_vendor_services';

    protected $fillable = [
        'shop_id',
        'vendor_service_id',
        'extra_details',
        'price',
        'price_unit',
        'duration_minutes',
        'schedule',
        'is_active',
    ];

    protected $casts = [
        'extra_details' => 'array',
        'schedule'      => 'array',
        'price'         => 'float',
        'is_active'     => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function vendorService()
    {
        return $this->belongsTo(VendorService::class);
    }

    /**
     * Check if service is open now
     */
    public function isOpenNow(): bool
    {
        if (!$this->schedule) return true;

        $day = strtolower(now()->englishDayOfWeek);
        $hours = $this->schedule[$day] ?? null;

        if (!$hours || ($hours['closed'] ?? false)) return false;

        $now = now()->format('H:i');
        return $now >= ($hours['open'] ?? '00:00') && $now <= ($hours['close'] ?? '23:59');
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
