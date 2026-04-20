<?php

namespace App\Services\Base;

use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Area;
use App\Models\DeliveryDistanceRange;
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
        [$multiplier, $range] = self::resolveMultiplierByDistance($maxDistance);

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
     * Resolve multiplier dynamically from DB ranges.
     *
     * Matching rule:
     * distance >= min AND (distance < max OR max IS NULL)
     */
    private static function resolveMultiplierByDistance(float $distanceKm): array
    {
        $ranges = DeliveryDistanceRange::query()
            ->sorted()
            ->get();

        if ($ranges->isEmpty()) {
            throw new CustomExceptionWithMessage('لم يتم تعريف نطاقات المسافة للتوصيل', 422);
        }

        $matched = $ranges->first(function (DeliveryDistanceRange $range) use ($distanceKm) {
            $max = $range->max_distance;

            return $distanceKm >= $range->min_distance
                && ($max === null || $distanceKm < $max);
        });

        if (!$matched) {
            Log::warning('Distance did not match any configured range', [
                'distance_km' => $distanceKm,
            ]);
            throw new CustomExceptionWithMessage('المسافة لا تقع ضمن أي نطاق توصيل معرف', 422);
        }

        $maxLabel = $matched->max_distance === null
            ? 'INF'
            : rtrim(rtrim((string)$matched->max_distance, '0'), '.');

        $minLabel = rtrim(rtrim((string)$matched->min_distance, '0'), '.');

        return [(float)$matched->multiplier, "{$minLabel}-{$maxLabel} km"];
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
