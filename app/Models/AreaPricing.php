<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\Model;

class AreaPricing extends Model
{
    use AppliesAreaScope;
    protected $table = 'area_pricing';

    protected $fillable = [
        'area_id',
        'base_fee',
        'distance_0_5_multiplier',
        'distance_5_8_multiplier',
        'distance_8_plus_multiplier',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'base_fee' => 'float',
        'distance_0_5_multiplier' => 'float',
        'distance_5_8_multiplier' => 'float',
        'distance_8_plus_multiplier' => 'float',
        'is_active' => 'boolean',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    public function getMultiplierByDistance(float $distanceKm): float
    {
        if ($distanceKm <= 5) {
            return $this->distance_0_5_multiplier;
        }

        if ($distanceKm <= 8) {
            return $this->distance_5_8_multiplier;
        }

        return $this->distance_8_plus_multiplier;
    }

    public function calculateDistanceFee(float $distanceKm): float
    {
        return $this->base_fee * $this->getMultiplierByDistance($distanceKm);
    }

    //     $pricing = AreaPricing::where('area_id', $areaId)
    //     ->where('is_active', true)
    //     ->firstOrFail();
    //      $segmentFee = $pricing->calculateDistanceFee(6.2);

}
