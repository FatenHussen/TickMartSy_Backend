<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'en' => 'Services',
                'ar' => 'الخدمات',
            ],
            [
                'en' => 'Cleaning',
                'ar' => 'تنظيف',
            ],
            [
                'en' => 'Installation',
                'ar' => 'تركيب',
            ],
            [
                'en' => 'Delivery',
                'ar' => 'توصيل',
            ],
            [
                'en' => 'Exchange Available',
                'ar' => 'امكانية التبديل',
            ],
            [
                'en' => 'Return Available',
                'ar' => 'امكانية الترجيع',
            ],
            [
                'en' => 'Warranty Available',
                'ar' => 'يوجد كفالة',
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service,
            ]);
        }
    }
}
