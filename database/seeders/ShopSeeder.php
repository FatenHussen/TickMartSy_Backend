<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use App\Models\Area;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();
        $areas   = Area::all();

        if ($vendors->isEmpty() || $areas->isEmpty()) {
            return;
        }

        foreach ($vendors as $vendor) {
            Shop::create([
                'name' => [
                    'ar' => 'متجر ' . $vendor->getTranslation('name', 'ar'),
                    'en' => 'Shop ' . $vendor->getTranslation('name', 'en'),
                ],

                'description' => [
                    'ar' => 'وصف المتجر التابع لـ ' . $vendor->getTranslation('name', 'ar'),
                    'en' => 'Shop description for ' . $vendor->getTranslation('name', 'en'),
                ],

                'address' => [
                    'ar' => 'دمشق - شارع الثورة',
                    'en' => 'Damascus - Al Thawra Street',
                ],

                'phone' => '0111234567',
                'mobile' => '0999999999',
                'email' => 'shop' . $vendor->id . '@example.com',

                'lat' => 33.5138,
                'lng' => 36.2765,

                'area_id' => $areas->random()->id,

                'working_hours' => [
                    'monday'    => ['open' => '09:00', 'close' => '22:00'],
                    'tuesday'   => ['open' => '09:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                    'thursday'  => ['open' => '09:00', 'close' => '22:00'],
                    'friday'    => ['open' => '16:00', 'close' => '23:00'],
                    'saturday'  => ['open' => '09:00', 'close' => '23:00'],
                    'sunday'    => ['closed' => true],
                ],

                'logo' => 'shops/logos/logo-' . $vendor->id . '.png',

                'cover_images' => [
                    'shops/covers/cover1.png',
                    'shops/covers/cover2.png',
                ],

                'is_active' => true,
                'ratings_count' => rand(0, 50),
                'ratings_sum'   => rand(0, 250),

                'vendor_id' => $vendor->id,
            ]);
        }
    }
}
