<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class VendorShopSeeder extends Seeder
{
    public function run(): void
    {
        $areas = DB::table('areas')->pluck('id')->toArray();
        if (empty($areas)) {
            $this->command->warn('جدول areas فارغ! سيتم استخدام area_id = 1 كافتراضي.');
            $areas = [1];
        }

        $vendorsData = [
            [
                'name' => ['ar' => 'مجموعة الشام التجارية', 'en' => 'Al-Sham Trading Group'],
                'owner_name' => 'أحمد محمد الخالد',
                'owner_phone' => '+963933123456',
                'commercial_register' => '1234567890',
                'contract_date' => '2025-01-10',
                'contract_number' => 'VND-2025-001',
                'contract_duration_months' => 36,
                'commission_rate' => 6.50,
            ],
            [
                'name' => ['ar' => 'أسواق دمشق الحديثة', 'en' => 'Modern Damascus Markets'],
                'owner_name' => 'فاطمة علي الحسن',
                'owner_phone' => '+963944987654',
                'commercial_register' => '0987654321',
                'contract_date' => '2025-03-15',
                'contract_number' => 'VND-2025-002',
                'contract_duration_months' => 24,
                'commission_rate' => 5.00,
            ],
            [
                'name' => ['ar' => 'تجارة البركة', 'en' => 'Baraka Trading'],
                'owner_name' => 'خالد عمر الدباس',
                'owner_phone' => '+963999555221',
                'commercial_register' => null,
                'contract_date' => '2025-06-20',
                'contract_number' => 'VND-2025-003',
                'contract_duration_months' => 18,
                'commission_rate' => 7.00,
            ],
            [
                'name' => ['ar' => 'المتاجر الذهبية', 'en' => 'Golden Stores'],
                'owner_name' => 'لينا مازن قاسم',
                'owner_phone' => '+963922111333',
                'commercial_register' => '5544332211',
                'contract_date' => '2025-09-05',
                'contract_number' => 'VND-2025-004',
                'contract_duration_months' => 48,
                'commission_rate' => 5.75,
            ],
            [
                'name' => ['ar' => 'سوبر ماركت الريف', 'en' => 'Rif Supermarket'],
                'owner_name' => 'محمود يوسف الزين',
                'owner_phone' => '+963977888999',
                'commercial_register' => '6677889900',
                'contract_date' => '2025-11-01',
                'contract_number' => 'VND-2025-005',
                'contract_duration_months' => 12,
                'commission_rate' => 6.00,
            ],
        ];

        foreach ($vendorsData as $vendorData) {
            $vendor = Vendor::create([
                'name' => $vendorData['name'],
                'owner_name' => $vendorData['owner_name'],
                'owner_phone' => $vendorData['owner_phone'],
                'commercial_register' => $vendorData['commercial_register'],
                'contract_date' => $vendorData['contract_date'],
                'contract_number' => $vendorData['contract_number'],
                'contract_duration_months' => $vendorData['contract_duration_months'],
                'commission_rate' => $vendorData['commission_rate'],
                'logo' => null,
                'cover_images' => null,
                'is_active' => true,
            ]);

            $shopsCount = fake()->numberBetween(2, 5);

            for ($i = 1; $i <= $shopsCount; $i++) {
                Shop::create([
                    'vendor_id' => $vendor->id,
                    'name' => [
                        'ar' => $vendorData['name']['ar'] . " - فرع {$i}",
                        'en' => $vendorData['name']['en'] . " - Branch {$i}",
                    ],
                    'description' => [
                        'ar' => 'متجر متكامل يقدم جميع المنتجات الغذائية والاستهلاكية بأسعار منافسة وجودة عالية.',
                        'en' => 'A full-service store offering all groceries and consumer products at competitive prices and high quality.',
                    ],
                    'address' => [
                        'ar' => fake()->randomElement([
                            'دمشق - المزة - شارع الجامعة',
                            'دمشق - كفرسوسة - قرب السفارة',
                            'دمشق - المالكي - شارع بغداد',
                            'دمشق - برزة - الشارع الرئيسي',
                            'دمشق - ركن الدين - ساحة النجمة',
                        ]),
                        'en' => fake()->randomElement([
                            'Damascus - Mezzeh - University Street',
                            'Damascus - Kafersouseh - Near the Embassy',
                            'Damascus - Malki - Baghdad Street',
                            'Damascus - Barzeh - Main Street',
                            'Damascus - Rukn Al-Deen - Najma Square',
                        ]),
                    ],
                    'phone' => '011' . fake()->numberBetween(1000000, 9999999),
                    'mobile' => '+9639' . fake()->numberBetween(30000000, 99999999),
                    'email' => fake()->unique()->safeEmail(),
                    'lat' => fake()->randomFloat(8, 33.48, 33.55), 
                    'lng' => fake()->randomFloat(8, 36.24, 36.36),
                    'area_id' => fake()->randomElement($areas),
                    'working_hours' => [
                        'monday'    => ['open' => '08:00', 'close' => '22:00'],
                        'tuesday'   => ['open' => '08:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '22:00'],
                        'thursday'  => ['open' => '08:00', 'close' => '23:00'],
                        'friday'    => ['closed' => true],
                        'saturday'  => ['open' => '09:00', 'close' => '23:00'],
                        'sunday'    => ['open' => '09:00', 'close' => '22:00'],
                    ],
                    'logo' => null,
                    'cover_images' => [],
                    'is_active' => fake()->boolean(95),
                ]);
            }
        }

    }
}