<?php

namespace App\Services\Base;

use App\Models\Area;
use Illuminate\Support\Facades\Log;

class DeliveryPricingService
{
    /**
     * حساب سعر التوصيل
     */
    public static function calculateDeliveryFee(int $customerAreaId, array $sellerAreaIds): float
    {
        $customerArea = Area::findOrFail($customerAreaId);
        $sellerAreas  = Area::whereIn('id', $sellerAreaIds)->get();

        Log::info('Delivery pricing started', [
            'customer_area_id' => $customerAreaId,
            'seller_area_ids'  => $sellerAreaIds,
            'base_fee'         => $customerArea->base_fee,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Case 1: All sellers in same area as customer
        |--------------------------------------------------------------------------
        */
        if ($sellerAreas->every(fn($area) => $area->id === $customerAreaId)) {
            Log::info('All sellers are in the same area as customer', [
                'final_fee' => $customerArea->base_fee,
            ]);

            return $customerArea->base_fee;
        }

        /*
        |--------------------------------------------------------------------------
        | Case 2: Different areas → calculate max distance
        |--------------------------------------------------------------------------
        */
        $maxDistance = 0;
        $distances = [];

        foreach ($sellerAreas as $sellerArea) {
            $distance = self::getDistanceBetweenAreas($customerArea, $sellerArea);
            $distances[] = [
                'seller_area_id' => $sellerArea->id,
                'distance_km'    => round($distance, 2),
            ];

            if ($distance > $maxDistance) {
                $maxDistance = $distance;
            }
        }

        Log::info('Calculated distances between customer and sellers', [
            'distances'   => $distances,
            'maxDistance' => round($maxDistance, 2),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Determine multiplier
        |--------------------------------------------------------------------------
        */
        if ($maxDistance <= 5) {
            $multiplier = Area::MULTIPLIER_0_5;
            $range = '0-5 km';
        } elseif ($maxDistance <= 8) {
            $multiplier = Area::MULTIPLIER_5_8;
            $range = '5-8 km';
        } else {
            $multiplier = Area::MULTIPLIER_8_PLUS;
            $range = '8+ km';
        }

        $finalFee = round($customerArea->base_fee * $multiplier, 2);

        Log::info('Delivery fee calculated', [
            'distance_range' => $range,
            'multiplier'     => $multiplier,
            'base_fee'       => $customerArea->base_fee,
            'final_fee'      => $finalFee,
        ]);

        return $finalFee;
    }

    /**
     * حساب المسافة بين منطقتين (km)
     */
    private static function getDistanceBetweenAreas(Area $area1, Area $area2): float
    {
        if ($area1->lat && $area1->lng && $area2->lat && $area2->lng) {
            $latFrom = deg2rad($area1->lat);
            $lonFrom = deg2rad($area1->lng);
            $latTo   = deg2rad($area2->lat);
            $lonTo   = deg2rad($area2->lng);

            $earthRadius = 6371;

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(
                pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
            ));

            return $angle * $earthRadius;
        }

        // fallback distance
        Log::warning('Missing lat/lng for areas, using fallback distance', [
            'area_1' => $area1->id,
            'area_2' => $area2->id,
            'fallback_distance' => 6,
        ]);

        return 6.0;
    }
}
