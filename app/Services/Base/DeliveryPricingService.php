<?php

namespace App\Services\Base;

use App\Models\Area;

class DeliveryPricingService
{
    /**
     * حساب سعر التوصيل
     *
     * @param int $customerAreaId
     * @param array $sellerAreaIds
     * @return float
     */
    public static function calculateDeliveryFee(int $customerAreaId, array $sellerAreaIds): float
    {
        $customerArea = Area::findOrFail($customerAreaId);
        $sellerAreas = Area::whereIn('id', $sellerAreaIds)->get();

        // إذا كل البائعين في نفس منطقة العميل
        if ($sellerAreas->every(fn($area) => $area->id === $customerAreaId)) {
            return $customerArea->base_fee;
        }

        // إذا مناطق مختلفة → نطبق Multiplier حسب المسافة
        // هنا نفترض أنه عندنا دالة لحساب المسافة بين مركز المناطق
        $maxDistance = 0;
        foreach ($sellerAreas as $sellerArea) {
            $distance = self::getDistanceBetweenAreas($customerArea, $sellerArea);
            if ($distance > $maxDistance) {
                $maxDistance = $distance;
            }
        }

        // نحدد multiplier حسب المسافة
        if ($maxDistance <= 5) {
            $multiplier = Area::MULTIPLIER_0_5;
        } elseif ($maxDistance <= 8) {
            $multiplier = Area::MULTIPLIER_5_8;
        } else {
            $multiplier = Area::MULTIPLIER_8_PLUS;
        }

        return round($customerArea->base_fee * $multiplier, 2);
    }

    /**
     * دالة افتراضية لحساب المسافة بين منطقتين (km)
     *
     * @param Area $area1
     * @param Area $area2
     * @return float
     */
    private static function getDistanceBetweenAreas(Area $area1, Area $area2): float
    {
        // إذا عندك lat/lng يمكن تحسب المسافة الحقيقية
        if ($area1->lat && $area1->lng && $area2->lat && $area2->lng) {
            // Haversine formula
            $latFrom = deg2rad($area1->lat);
            $lonFrom = deg2rad($area1->lng);
            $latTo = deg2rad($area2->lat);
            $lonTo = deg2rad($area2->lng);

            $earthRadius = 6371; // km

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            return $angle * $earthRadius;
        }

        // إذا ما في إحداثيات → تقدير ثابت (مثلاً 6 كم)
        return 6.0;
    }
}
