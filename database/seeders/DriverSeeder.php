<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\City;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityIds = City::query()->pluck('id')->values();
        $shopIds = Shop::query()->pluck('id')->values();
        $vendorIds = Vendor::query()->pluck('id')->values();

        $drivers = [
            [
                'name' => 'حمزة فواز',
                'phone' => '0993359825',
                'email' => 'hamzafz888@gmail.com',
                'password' => Hash::make('123456'),
                'address' => 'شارع الملك فيصل، وسط البلد',
                'status' => 'available',
                'rate_per_order' => 15.50,
                'vehicle_type' => 'motorcycle',
                'vehicle_name' => 'Honda CB 150',
                'vehicle_number' => 'ABC-123',
                'is_active' => true,
                'city_indexes' => [0, 1],
                'shop_indexes' => [0],
                'vendor_indexes' => [0],
            ],
            [
                'name' => 'محمد علي',
                'phone' => '0987654321',
                'password' => Hash::make('123456'),
                'address' => 'شارع الجامعة، منطقة الجامعة',
                'status' => 'busy',
                'rate_per_order' => 18.00,
                'vehicle_type' => 'car',
                'vehicle_name' => 'Kia Rio',
                'vehicle_number' => 'XYZ-789',
                'is_active' => true,
                'city_indexes' => [1, 2],
                'shop_indexes' => [1],
                'vendor_indexes' => [0],
            ],
            [
                'name' => 'خالد أحمد',
                'phone' => '0555123456',
                'password' => Hash::make('123456'),
                'address' => 'شارع الحرية، المنطقة الشرقية',
                'status' => 'available',
                'rate_per_order' => 12.00,
                'vehicle_type' => 'bicycle',
                'vehicle_name' => 'Giant Escape 3',
                'vehicle_number' => 'BIC-456',
                'is_active' => true,
                'city_indexes' => [0],
                'shop_indexes' => [2],
                'vendor_indexes' => [0],
            ],
            [
                'name' => 'عمر حسن',
                'phone' => '0777888999',
                'password' => Hash::make('123456'),
                'address' => 'شارع النصر، المنطقة الغربية',
                'status' => 'inactive',
                'rate_per_order' => 20.00,
                'vehicle_type' => 'car',
                'vehicle_name' => 'Hyundai Accent',
                'vehicle_number' => 'DEF-321',
                'is_active' => false,
                'city_indexes' => [2],
                'shop_indexes' => [],
                'vendor_indexes' => [],
            ],
            [
                'name' => 'يوسف سالم',
                'phone' => '0666777888',
                'password' => Hash::make('123456'),
                'address' => 'شارع العروبة، المنطقة الجنوبية',
                'status' => 'available',
                'rate_per_order' => 16.75,
                'vehicle_type' => 'motorcycle',
                'vehicle_name' => 'Yamaha FZ',
                'vehicle_number' => 'GHI-654',
                'is_active' => true,
                'city_indexes' => [0, 2],
                'shop_indexes' => [0, 1, 2],
                'vendor_indexes' => [0],
            ],
        ];

        foreach ($drivers as $driverData) {
            $cityIndexes = $driverData['city_indexes'] ?? [];
            $shopIndexes = $driverData['shop_indexes'] ?? [];
            $vendorIndexes = $driverData['vendor_indexes'] ?? [];

            unset($driverData['city_indexes'], $driverData['shop_indexes'], $driverData['vendor_indexes']);

            $driver = Driver::updateOrCreate(
                ['phone' => $driverData['phone']],
                $driverData
            );

            $selectedCityIds = collect($cityIndexes)
                ->map(fn (int $index) => $cityIds->get($index))
                ->filter()
                ->values()
                ->all();

            $selectedShopIds = collect($shopIndexes)
                ->map(fn (int $index) => $shopIds->get($index))
                ->filter()
                ->values()
                ->all();

            $selectedVendorIds = collect($vendorIndexes)
                ->map(fn (int $index) => $vendorIds->get($index))
                ->filter()
                ->values()
                ->all();

            $driver->cities()->sync($selectedCityIds);
            $driver->shops()->sync($selectedShopIds);
            $driver->vendors()->sync($selectedVendorIds);
        }

        $this->command->info('Created/updated ' . count($drivers) . ' drivers with full city/shop/vendor assignments.');
    }
}
