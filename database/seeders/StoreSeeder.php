<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\Category;
use App\Models\Area;
use App\Models\Service;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id')->toArray();
        $areas      = Area::pluck('id')->toArray();
        $services   = Service::pluck('id')->toArray();

        for ($i = 1; $i <= 3; $i++) {
            $store = Store::create([
                'name' => [
                    'ar' => "متجر رقم {$i}",
                    'en' => "Store {$i}",
                ],
                'owner_name'   => 'Owner Name',
                'owner_phone'  => '0999999999',
                'description'  => [
                    'ar' => 'وصف تجريبي',
                    'en' => 'Demo description',
                ],
                'address'      => [
                    'ar' => 'دمشق',
                    'en' => 'Damascus',
                ],
                'phone'        => '011000000',
                'mobile'       => '0999999999',
                'email'        => "store{$i}@example.com",
                'commercial_register' => 'CR-0000',
                'contract_date' => now(),
                'contract_number' => "CNT-000{$i}",
                'contract_duration_months' => 12,
                'commission_rate' => 5,
                'working_hours' => [
                    'monday' => [
                        'open' => '09:00',
                        'close' => '22:00',
                    ],
                    'tuesday' => [
                        'open' => '09:00',
                        'close' => '22:00',
                    ],
                ],
                'is_active' => true,
            ]);

            if ($categories) {
                $store->categories()->sync($categories);
            }

            if ($areas) {
                $store->areas()->sync($areas);
            }

            if ($services) {
                $store->services()->sync($services);
            }

            // Media (Logo)
            $store->media()->create([
                'collection' => 'logo',
                'file_name'  => 'logo.png',
                'path'  => 'media/logo/logo.png',
                'file_type'  => 'image/png',
                'order'      => 0,
                'is_active'  => true,
            ]);

            // Media (Cover Images)
            foreach ([0, 1, 2] as $index) {
                $store->media()->create([
                    'collection' => 'cover',
                    'file_name'  => "cover{$index}.jpg",
                    'path'  => "media/cover/cover{$index}.jpg",
                    'file_type'  => 'image/jpeg',
                    'order'      => $index,
                    'is_active'  => true,
                ]);
            }
        }
    }
}
