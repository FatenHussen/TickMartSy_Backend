<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\VendorService;
use App\Models\VendorServiceType;
use App\Models\ShopVendorService;
use Illuminate\Database\Seeder;

class VendorServiceSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::all();

        if ($shops->isEmpty()) {
            return;
        }

        $schedule = [
            'monday' => ['open' => '08:00', 'close' => '21:00'],
            'tuesday' => ['open' => '08:00', 'close' => '21:00'],
            'wednesday' => ['open' => '08:00', 'close' => '21:00'],
            'thursday' => ['open' => '08:00', 'close' => '21:00'],
            'friday' => ['open' => '09:00', 'close' => '20:00'],
            'saturday' => ['open' => '09:00', 'close' => '20:00'],
            'sunday' => ['closed' => true],
        ];

        $types = [
            [
                'name' => ['en' => 'Home Services', 'ar' => 'ÎÏãÇÊ ãäÒáíÉ'],
                'services' => [
                    [
                        'name' => ['en' => 'Home Cleaning', 'ar' => 'ÊäÙíİ ãäÒáí'],
                        'description' => [
                            'en' => 'General cleaning for one visit, includes vacuuming, dusting, and sanitizing surfaces.',
                            'ar' => 'ÊäÙíİ ÚÇã ááãäÒá íÔãá ÇáÊäÙíİ ÈÇáãßäÓÉ ÇáßåÑÈÇÆíÉ æÊäÙíİ ÇáÃÓØÍ æÊÚŞíãåÇ.',
                        ],
                        'base_price' => 4500,
                        'price_unit' => 'per visit',
                        'duration_minutes' => 90,
                    ],
                    [
                        'name' => ['en' => 'Carpet Steam Cleaning', 'ar' => 'ÊäÙíİ ÇáÓÌÇÏ ÈÇáÈÎÇÑ'],
                        'description' => [
                            'en' => 'Steam cleaning for carpets and rugs, ideal for removing stains and deep dirt.',
                            'ar' => 'ÊäÙíİ ÈÇáÈÎÇÑ ááÓÌÇÏ æÓÌÇÏ ÇáÃÑÖíÇÊ áÅÒÇáÉ ÇáÈŞÚ æÇáÔæÇÆÈ ÇáÚãíŞÉ.',
                        ],
                        'base_price' => 6000,
                        'price_unit' => 'per visit',
                        'duration_minutes' => 120,
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Installation & Setup', 'ar' => 'ÇáÊÑßíÈ æÇáÊÌåíÒ'],
                'services' => [
                    [
                        'name' => ['en' => 'TV & Appliance Installation', 'ar' => 'ÊÑßíÈ ÃÌåÒÉ ÇáÊáíİÒíæä æÇáÃÌåÒÉ'],
                        'description' => [
                            'en' => 'Mounting TVs, hooking up AV equipment, and calibrating the first setup.',
                            'ar' => 'ÊÑßíÈ ÇáÊáİÒíæäÇÊ æÊæÕíá ÇáÃÌåÒÉ ÇáÕæÊíÉ æÖÈØ ÅÚÏÇÏÇÊ ÇáÊÔÛíá ÇáÃæáì.',
                        ],
                        'base_price' => 7000,
                        'price_unit' => 'per visit',
                        'duration_minutes' => 120,
                    ],
                ],
            ],
        ];

        $vendorServices = [];

        foreach ($types as $typeData) {
            $type = VendorServiceType::updateOrCreate(
                ['name->en' => $typeData['name']['en']],
                ['name' => $typeData['name'], 'is_active' => true]
            );

            foreach ($typeData['services'] as $serviceData) {
                $vendorService = VendorService::updateOrCreate(
                    [
                        'vendor_service_type_id' => $type->id,
                        'name->en' => $serviceData['name']['en'],
                    ],
                    [
                        'name' => $serviceData['name'],
                        'description' => $serviceData['description'],
                        'is_active' => true,
                    ]
                );

                $vendorServices[] = array_merge($vendorService->toArray(), [
                    'base_price' => $serviceData['base_price'],
                    'price_unit' => $serviceData['price_unit'],
                    'duration_minutes' => $serviceData['duration_minutes'],
                ]);
            }
        }

        if (empty($vendorServices)) {
            return;
        }

        foreach ($shops as $shopIndex => $shop) {
            foreach ($vendorServices as $serviceIndex => $serviceData) {
                ShopVendorService::updateOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'vendor_service_id' => $serviceData['id'],
                    ],
                    [
                        'extra_details' => [
                            'notes' => 'Available for Tikmool customers',
                            'team' => 'Pro crew',
                        ],
                        'price' => $serviceData['base_price'] + ($shopIndex * 500) + ($serviceIndex * 250),
                        'price_unit' => $serviceData['price_unit'],
                        'duration_minutes' => $serviceData['duration_minutes'] + ($shopIndex * 10),
                        'schedule' => $schedule,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
