<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use App\Models\VendorPackage;
use App\Models\VendorSubscription;
use Illuminate\Database\Seeder;

class VendorSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Vendor::all();
        $packages = VendorPackage::all();

        if ($shops->isEmpty() || $packages->isEmpty()) {
            $this->command->warn('يجب تشغيل ShopSeeder و VendorPackageSeeder أولاً.');
            return;
        }

        $subscriptions = [];

        foreach ($shops as $index => $shop) {
            $package = $packages[$index % $packages->count()];

            $startsAt = now()->subDays(rand(5, 60));
            $endsAt = $startsAt->copy()->addDays($package->duration_days);

            $statuses = ['active', 'active', 'pending', 'expired'];
            $status = $statuses[array_rand($statuses)];

            if ($status === 'expired') {
                $endsAt = now()->subDays(rand(1, 30));
            } elseif ($status === 'pending') {
                $startsAt = now()->addDays(rand(1, 7));
                $endsAt = $startsAt->copy()->addDays($package->duration_days);
            }

            $subscriptions[] = [
                'vendor_id' => $shop->id,
                'vendor_package_id' => $package->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'auto_renew' => (bool) rand(0, 1),
                'status' => $status,
                'notes' => rand(0, 1) ? 'اشتراك تجريبي' : null,
            ];
        }

        foreach ($subscriptions as $data) {
            VendorSubscription::firstOrCreate(
                [
                    'vendor_id' => $data['shop_id'],
                    'vendor_package_id' => $data['vendor_package_id'],
                    'starts_at' => $data['starts_at'],
                ],
                $data
            );
        }

        $extraCount = min(3, $shops->count() * 2);
        for ($i = 0; $i < $extraCount; $i++) {
            $shop = $shops->random();
            $package = $packages->random();
            $startsAt = now()->subDays(rand(60, 180));
            $endsAt = $startsAt->copy()->addDays($package->duration_days);

            VendorSubscription::firstOrCreate(
                [
                    'vendor_id' => $shop->id,
                    'vendor_package_id' => $package->id,
                    'starts_at' => $startsAt,
                ],
                [
                    'ends_at' => $endsAt,
                    'auto_renew' => false,
                    'status' => 'expired',
                    'notes' => 'اشتراك سابق',
                ]
            );
        }

        $count = VendorSubscription::count();
        $this->command->info("تم إنشاء {$count} اشتراك لباقات المتاجر.");
    }
}
