<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorUserSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();

        foreach ($vendors as $vendor) {
            VendorUser::create([
                'name' => $vendor->getTranslation('name', 'en') . ' Admin',
                'email' => 'vendor' . $vendor->id . '@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'vendor_id' => $vendor->id,
            ]);
        }
    }
}
