<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAddress;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        UserAddress::insert([
            [
                'user_id' => 1,
                'label' => 'Home',
                'area_id' => 1,
                'street_name' => 'Al Hamra Street',
                'nearest_landmark' => 'Near City Mall',
                'building_number' => '12A',
                'floor_apartment' => '3rd Floor - Apt 5',
                'contact_phone' => '0599123456',
                'lat' => 33.513807,
                'lng' => 36.276528,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'label' => 'Work',
                'area_id' => 1,
                'street_name' => 'Mazzeh Highway',
                'nearest_landmark' => 'Opposite ABC Bank',
                'building_number' => '45',
                'floor_apartment' => '5th Floor',
                'contact_phone' => '0599345678',
                'lat' => 33.507555,
                'lng' => 36.264233,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'label' => 'Parents House',
                'area_id' => 2,
                'street_name' => 'Old Market Street',
                'nearest_landmark' => 'Near Old Mosque',
                'building_number' => null,
                'floor_apartment' => 'Ground Floor',
                'contact_phone' => '0599554433',
                'lat' => 33.514900,
                'lng' => 36.277800,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
