<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendor =   Vendor::create([
            'name' => [
                'ar' => 'تيكمول',
                'en' => 'Tikmool',
            ],
            'owner_name' => 'Tikmool Admin',
            'owner_phone' => '0990000000',
            'commercial_register' => 'CR-TIKMOOL-001',
            'contract_date' => now()->subYear(),
            'contract_number' => 'TIK-CNT-001',
            'contract_duration_months' => 36,
            'commission_rate' => 10.00,
            'is_active' => true,
            'ratings_count' => 0,
            'ratings_sum' => 0,
        ]);

        $vendor->badges()->sync([1, 2, 3]);
    }
}
