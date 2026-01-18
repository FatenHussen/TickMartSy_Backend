<?php

namespace App\Services\Base;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;

class LocationService
{
    public function getNearestShopId(?float $lat = null, ?float $lng = null): ?int
    {
        // $lat = $lat ?? request()->query('lat');
        // $lng = $lng ?? request()->query('lng');

        // if (!$lat || !$lng) {
        //     $location = $this->getLocationFromIp(request()->ip());
        //     $lat = $location['lat'] ?? null;
        //     $lng = $location['lng'] ?? null;
        // }

        if (!$lat || !$lng) {
            return null;
        }

        return $this->findNearestShop($lat, $lng);
    }

    protected function findNearestShop(float $lat, float $lng): ?int
    {
        return Shop::selectRaw("
            id,
            (
                6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
            ->orderBy('distance')
            ->value('id');
    }

    protected function getLocationFromIp(string $ip): ?array
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'lat' => $data['lat'] ?? null,
                    'lng' => $data['lon'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            // تجاهل الخطأ
        }

        return null;
    }
}
