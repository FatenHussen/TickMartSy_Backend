<?php

namespace Database\Seeders;

use App\Models\SellerRegistration;
use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some cities and governorates for testing
        $governorate = Governorate::first();
        $city = City::first();
        $country = Country::first();

        if (!$governorate || !$city || !$country) {
            $this->command->warn('Please seed countries, governorates, and cities first!');
            return;
        }

        $registrations = [
            [
                'email' => 'seller1@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'أحمد محمد',
                'store_name' => 'متجر الإلكترونيات الحديثة',
                'registered_at' => now()->subDays(5),
                'address' => 'شارع الملك فهد، حي العليا',
                'commercial_register_number' => 'CR2024001',
                'commercial_register_date' => '2024-01-15',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller2@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'فاطمة علي',
                'store_name' => 'متجر الأزياء العصرية',
                'registered_at' => now()->subDays(3),
                'address' => 'طريق الأمير محمد بن عبدالعزيز',
                'commercial_register_number' => 'CR2024002',
                'commercial_register_date' => '2024-01-20',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller3@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'خالد عبدالله',
                'store_name' => 'متجر الأثاث المنزلي',
                'registered_at' => now()->subDays(7),
                'address' => 'شارع التحلية، حي السليمانية',
                'commercial_register_number' => 'CR2024003',
                'commercial_register_date' => '2024-01-10',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'approved',
            ],
            [
                'email' => 'seller4@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'سارة حسن',
                'store_name' => 'متجر مستحضرات التجميل',
                'registered_at' => now()->subDays(10),
                'address' => 'طريق الملك عبدالله، حي الربوة',
                'commercial_register_number' => 'CR2024004',
                'commercial_register_date' => '2024-01-05',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'rejected',
            ],
            [
                'email' => 'seller5@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'محمد سعيد',
                'store_name' => 'متجر الرياضة واللياقة',
                'registered_at' => now()->subDays(2),
                'address' => 'شارع العروبة، حي المروج',
                'commercial_register_number' => 'CR2024005',
                'commercial_register_date' => '2024-02-01',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller6@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'نورة إبراهيم',
                'store_name' => 'متجر الكتب والقرطاسية',
                'registered_at' => now()->subDays(4),
                'address' => 'طريق الملك فيصل، حي النزهة',
                'commercial_register_number' => 'CR2024006',
                'commercial_register_date' => '2024-01-25',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller7@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'عبدالرحمن أحمد',
                'store_name' => 'متجر الألعاب والترفيه',
                'registered_at' => now()->subDays(6),
                'address' => 'شارع الأمير سلطان، حي الملز',
                'commercial_register_number' => 'CR2024007',
                'commercial_register_date' => '2024-01-18',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller8@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'ليلى محمود',
                'store_name' => 'متجر الإكسسوارات النسائية',
                'registered_at' => now()->subDays(8),
                'address' => 'طريق الملك خالد، حي الياسمين',
                'commercial_register_number' => 'CR2024008',
                'commercial_register_date' => '2024-01-12',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'approved',
            ],
            [
                'email' => 'seller9@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'يوسف عمر',
                'store_name' => 'متجر الأجهزة المنزلية',
                'registered_at' => now()->subDays(1),
                'address' => 'شارع الأمير ماجد، حي الروضة',
                'commercial_register_number' => 'CR2024009',
                'commercial_register_date' => '2024-02-05',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'pending',
            ],
            [
                'email' => 'seller10@example.com',
                'password' => Hash::make('password123'),
                'seller_name' => 'هند عبدالعزيز',
                'store_name' => 'متجر الهدايا والتحف',
                'registered_at' => now()->subDays(9),
                'address' => 'طريق الملك عبدالله، حي الورود',
                'commercial_register_number' => 'CR2024010',
                'commercial_register_date' => '2024-01-08',
                'country_id' => $country->id,
                'city_id' => $city->id,
                'governorate_id' => $governorate->id,
                'logo' => null,
                'status' => 'rejected',
            ],
        ];

        foreach ($registrations as $registration) {
            SellerRegistration::create($registration);
        }

        $this->command->info('✅ Created 10 seller registrations successfully!');
        $this->command->info('   - 6 Pending registrations');
        $this->command->info('   - 2 Approved registrations');
        $this->command->info('   - 2 Rejected registrations');
    }
}
