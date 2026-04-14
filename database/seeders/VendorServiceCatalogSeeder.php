<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopVendorService;
use App\Models\VendorService;
use App\Models\VendorServiceType;
use Illuminate\Database\Seeder;

class VendorServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => ['en' => 'Home Services', 'ar' => 'خدمات منزلية'],
                'services' => [
                    ['en' => 'Home Cleaning', 'ar' => 'تنظيف منازل'],
                    ['en' => 'Deep Cleaning', 'ar' => 'تنظيف عميق'],
                    ['en' => 'Ironing Service', 'ar' => 'خدمة كوي'],
                ],
            ],
            [
                'name' => ['en' => 'Maintenance', 'ar' => 'صيانة'],
                'services' => [
                    ['en' => 'Electrical Maintenance', 'ar' => 'صيانة كهرباء'],
                    ['en' => 'Plumbing', 'ar' => 'سباكة'],
                    ['en' => 'Air Conditioner Service', 'ar' => 'صيانة مكيفات'],
                ],
            ],
            [
                'name' => ['en' => 'Personal Care', 'ar' => 'عناية شخصية'],
                'services' => [
                    ['en' => 'Haircut at Home', 'ar' => 'حلاقة منزلية'],
                    ['en' => 'Massage Session', 'ar' => 'جلسة مساج'],
                ],
            ],
        ];

        $serviceModels = collect();

        foreach ($types as $typeData) {
            $type = $this->firstOrCreateType($typeData['name']);

            foreach ($typeData['services'] as $serviceName) {
                $serviceModels->push(
                    $this->firstOrCreateService($type, $serviceName)
                );
            }
        }

        // Ensure there are service-provider shops to attach catalog items.
        $providerShops = Shop::query()
            ->where('is_active', true)
            ->where('is_service_provider', true)
            ->get();

        if ($providerShops->isEmpty()) {
            $providerShops = Shop::query()->where('is_active', true)->take(2)->get();
            foreach ($providerShops as $shop) {
                $shop->update(['is_service_provider' => true]);
            }
        }

        if ($providerShops->isEmpty() || $serviceModels->isEmpty()) {
            return;
        }

        foreach ($providerShops as $shop) {
            foreach ($serviceModels as $service) {
                ShopVendorService::updateOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'vendor_service_id' => $service->id,
                    ],
                    [
                        'extra_details' => [
                            'en' => 'Available with prior booking.',
                            'ar' => 'متاح بالحجز المسبق.',
                        ],
                        'price' => rand(25, 250),
                        'price_unit' => 'per service',
                        'duration_minutes' => rand(30, 120),
                        'schedule' => $this->defaultSchedule(),
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function firstOrCreateType(array $name): VendorServiceType
    {
        $existing = VendorServiceType::query()->get()->first(function (VendorServiceType $type) use ($name) {
            return $type->getTranslation('name', 'en') === $name['en'];
        });

        if ($existing) {
            return $existing;
        }

        return VendorServiceType::create([
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function firstOrCreateService(VendorServiceType $type, array $name): VendorService
    {
        $existing = VendorService::query()
            ->where('vendor_service_type_id', $type->id)
            ->get()
            ->first(function (VendorService $service) use ($name) {
                return $service->getTranslation('name', 'en') === $name['en'];
            });

        if ($existing) {
            return $existing;
        }

        return VendorService::create([
            'vendor_service_type_id' => $type->id,
            'name' => $name,
            'description' => [
                'en' => 'Professional service delivered by trained staff.',
                'ar' => 'خدمة احترافية مقدمة من فريق متخصص.',
            ],
            'is_active' => true,
        ]);
    }

    private function defaultSchedule(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'friday' => ['open' => '10:00', 'close' => '16:00', 'closed' => false],
            'saturday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'sunday' => ['open' => null, 'close' => null, 'closed' => true],
        ];
    }
}
