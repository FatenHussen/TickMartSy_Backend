<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available cities or use null if none exist
        $cityIds = Area::pluck('id')->toArray();
        $defaultCityId = !empty($cityIds) ? $cityIds[0] : null;

        $users = [
            [
                'name' => 'محمد خزعة',
                'phone' => '0957432443',
                'email' => 'mhmd@gmail.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => $defaultCityId,
                'created_at' => Carbon::now()->subDays(60),
                'updated_at' => Carbon::now()->subDays(60),
                'is_affiliate' => true,
                'affiliate_approved' => true,
                'affiliate_id' => '34567',
                'affiliate_rate' => '20',
            ],
            [
                'name' => 'حمزة فواز',
                'phone' => '0993359825',
                'email' => 'hamza@gmail.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => $defaultCityId,
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(45),
                'is_affiliate' => true,
                'affiliate_approved' => true,
                'affiliate_id' => '12567',
                'affiliate_rate' => '20',
            ],
            [
                'name' => 'محمد حسن',
                'phone' => '0555123456',
                'email' => 'mohammed@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => !empty($cityIds) && count($cityIds) > 1 ? $cityIds[1] : $defaultCityId,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
                'is_affiliate' => true,
                'affiliate_approved' => true,
                'affiliate_id' => '12345',
                'affiliate_rate' => '20',
            ],
            [
                'name' => 'سارة أحمد',
                'phone' => '0777888999',
                'email' => 'sara@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => $defaultCityId,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'name' => 'خالد يوسف',
                'phone' => '0666777888',
                'email' => 'khalid@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => !empty($cityIds) && count($cityIds) > 1 ? $cityIds[1] : $defaultCityId,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'name' => 'نور الدين',
                'phone' => '0444555666',
                'email' => 'nour@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => $defaultCityId,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'name' => 'ليلى محمود',
                'phone' => '0333444555',
                'email' => 'layla@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => !empty($cityIds) && count($cityIds) > 1 ? $cityIds[1] : $defaultCityId,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'name' => 'عمر سالم',
                'phone' => '0222333444',
                'email' => 'omar@example.com',
                'password' => Hash::make('123456'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'area_id' => $defaultCityId,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Created ' . count($users) . ' test users for point system.');
    }
}
