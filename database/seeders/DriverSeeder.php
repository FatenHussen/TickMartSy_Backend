<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'حمزة فواز',
                'phone' => '
                ',
                'password' => Hash::make('123456'),
                'address' => 'شارع الملك فيصل، وسط البلد',
                'status' => 'available',
                'rate_per_order' => 15.50,
                'vehicle_type' => 'motorcycle',
                'vehicle_number' => 'ABC-123',
                'is_active' => true,
            ],
            [
                'name' => 'محمد علي',
                'phone' => '0987654321',
                'password' => Hash::make('123456'),
                'address' => 'شارع الجامعة، منطقة الجامعة',
                'status' => 'busy',
                'rate_per_order' => 18.00,
                'vehicle_type' => 'car',
                'vehicle_number' => 'XYZ-789',
                'is_active' => true,
            ],
            [
                'name' => 'خالد أحمد',
                'phone' => '0555123456',
                'password' => Hash::make('123456'),
                'address' => 'شارع الحرية، المنطقة الشرقية',
                'status' => 'available',
                'rate_per_order' => 12.00,
                'vehicle_type' => 'bicycle',
                'vehicle_number' => 'BIC-456',
                'is_active' => true,
            ],
            [
                'name' => 'عمر حسن',
                'phone' => '0777888999',
                'password' => Hash::make('123456'),
                'address' => 'شارع النصر، المنطقة الغربية',
                'status' => 'inactive',
                'rate_per_order' => 20.00,
                'vehicle_type' => 'car',
                'vehicle_number' => 'DEF-321',
                'is_active' => false,
            ],
            [
                'name' => 'يوسف سالم',
                'phone' => '0666777888',
                'password' => Hash::make('123456'),
                'address' => 'شارع العروبة، المنطقة الجنوبية',
                'status' => 'available',
                'rate_per_order' => 16.75,
                'vehicle_type' => 'motorcycle',
                'vehicle_number' => 'GHI-654',
                'is_active' => true,
            ],
        ];

        foreach ($drivers as $driverData) {
            $driver = Driver::create($driverData);

            // Assign random areas to each driver (1-3 areas)
            $areas = Area::inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($areas->isNotEmpty()) {
                $driver->areas()->attach($areas);
            }
        }

        $this->command->info('Created ' . count($drivers) . ' drivers with random area assignments.');
    }
}
