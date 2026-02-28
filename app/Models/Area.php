<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'city_id',
        'base_fee',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'base_fee' => 'float',
        'is_active' => 'boolean',
    ];

    // =====================================================
    // Multipliers ثابتة حسب المسافة
    // =====================================================
    const MULTIPLIER_0_5 = 1.5;    // ≤ 5 كم
    const MULTIPLIER_5_8 = 2.0;    // >5 و ≤8 كم
    const MULTIPLIER_8_PLUS = 3.0; // >8 كم

    // =====================================================
    // العلاقات
    // =====================================================
    public function stores()
    {
        return $this->hasMany(Shop::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // =====================================================
    // Delivery Fee
    // =====================================================

    /**
     * Inject distance temporarily for calculation
     */
    public function withDistance(float $distanceKm): self
    {
        $this->attributes['distance_km'] = $distanceKm;
        return $this;
    }

    /**
     * Accessor لحساب Delivery Fee حسب المسافة
     */
    public function getDeliveryFeeAttribute(): float
    {
        if (!isset($this->attributes['distance_km'])) {
            return $this->base_fee;
        }

        $distance = $this->attributes['distance_km'];

        if ($distance <= 5) {
            $multiplier = self::MULTIPLIER_0_5;
        } elseif ($distance <= 8) {
            $multiplier = self::MULTIPLIER_5_8;
        } else {
            $multiplier = self::MULTIPLIER_8_PLUS;
        }

        return round($this->base_fee * $multiplier, 2);
    }

    /**
     * حساب عدة مقاطع (Multi-stop delivery)
     * segments = [km1, km2, km3...]
     */
    public function calculateMultiStopFee(array $segments): float
    {
        $total = $this->base_fee; // Base Fee مرة واحدة للمنطقة

        foreach ($segments as $km) {
            $total += $this->withDistance($km)->delivery_fee;
        }

        return round($total, 2);
    }
}
