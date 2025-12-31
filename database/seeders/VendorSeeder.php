<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => [
                    'ar' => 'مطاعم الشام',
                    'en' => 'Sham Restaurants',
                ],
                'owner_name' => 'أحمد علي',
                'owner_phone' => '0999999999',
                'commercial_register' => 'CR-1001',
                'contract_date' => now()->subMonths(6),
                'contract_number' => 'CNT-001',
                'contract_duration_months' => 12,
                'commission_rate' => 10.00,
                'is_active' => true,
                'ratings_count' => 20,
                'ratings_sum' => 90,
            ],
            [
                'name' => [
                    'ar' => 'ماركت المدينة',
                    'en' => 'City Market',
                ],
                'owner_name' => 'محمد حسن',
                'owner_phone' => '0988888888',
                'commercial_register' => 'CR-1002',
                'contract_date' => now()->subMonths(3),
                'contract_number' => 'CNT-002',
                'contract_duration_months' => 24,
                'commission_rate' => 12.50,
                'is_active' => true,
                'ratings_count' => 15,
                'ratings_sum' => 60,
            ],
            [
                'name' => [
                    'ar' => 'حلويات الشرق',
                    'en' => 'Orient Sweets',
                ],
                'owner_name' => 'خالد محمود',
                'owner_phone' => '0977777777',
                'commercial_register' => 'CR-1003',
                'contract_date' => now()->subMonths(1),
                'contract_number' => 'CNT-003',
                'contract_duration_months' => 6,
                'commission_rate' => 8.00,
                'is_active' => false,
                'ratings_count' => 0,
                'ratings_sum' => 0,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}
