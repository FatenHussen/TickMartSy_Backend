<?php

namespace Database\Seeders;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ShopVendorService;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(4)->get();
        $shopVendorServices = ShopVendorService::where('is_active', true)->take(4)->get();

        if ($users->count() < 4 || $shopVendorServices->count() < 4) {
            return;
        }

        $statuses = [
            ServiceOrderStatus::PENDING,
            ServiceOrderStatus::COMPLETED,
            ServiceOrderStatus::CANCELED,
            ServiceOrderStatus::REJECTED,
        ];

        foreach ($statuses as $index => $status) {
            $user = $users->get($index);
            $shopVendorService = $shopVendorServices->get($index);

            if (! $user || ! $shopVendorService) {
                continue;
            }

            ServiceOrder::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'shop_vendor_service_id' => $shopVendorService->id,
                    'status' => $status->value,
                ],
                [
                    'shop_id' => $shopVendorService->shop_id,
                    'vendor_service_id' => $shopVendorService->vendor_service_id,
                    'price' => $shopVendorService->price ?? 5000,
                    'price_unit' => $shopVendorService->price_unit ?? 'per visit',
                    'notes' => "Automatically seeded order ({$status->labelEn()}).",
                ]
            );
        }
    }
}
