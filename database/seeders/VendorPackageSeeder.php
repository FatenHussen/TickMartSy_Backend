<?php

namespace Database\Seeders;

use App\Models\VendorPackage;
use Illuminate\Database\Seeder;

class VendorPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => ['en' => 'Basic', 'ar' => 'أساسي'],
                'description' => ['en' => 'For small vendors', 'ar' => 'للتجار الصغار'],
                'price' => 49,
                'duration_days' => 30,
                'max_products' => 50,
                'is_featured' => false,
                'has_premium_badge' => false,
                'search_priority' => 1,
                'max_campaigns' => 5,
                'has_banner_ad' => false,
                'has_sales_reports' => true,
                'has_analytics' => false,
                'report_level' => 'basic',
                'order_priority' => 1,
                'can_set_prep_time' => false,
                'custom_shipping_options' => false,
                'has_vendor_delivery' => false,
                'commission_rate' => 5,
                'commission_per_order' => 0,
                'activation_fee_waived' => false,
            ],
            [
                'name' => ['en' => 'Pro', 'ar' => 'متقدم'],
                'description' => ['en' => 'For growing businesses', 'ar' => 'للشركات النامية'],
                'price' => 149,
                'duration_days' => 30,
                'max_products' => 500,
                'is_featured' => true,
                'has_premium_badge' => false,
                'search_priority' => 5,
                'max_campaigns' => 5,
                'has_banner_ad' => false,
                'has_sales_reports' => true,
                'has_analytics' => true,
                'report_level' => 'advanced',
                'order_priority' => 5,
                'can_set_prep_time' => true,
                'custom_shipping_options' => true,
                'has_vendor_delivery' => false,
                'commission_rate' => 3.5,
                'commission_per_order' => 0.5,
                'activation_fee_waived' => false,
            ],
            [
                'name' => ['en' => 'Premium', 'ar' => 'بريميوم'],
                'description' => ['en' => 'Full features for large vendors', 'ar' => 'مزايا كاملة للتجار الكبار'],
                'price' => 299,
                'duration_days' => 30,
                'max_products' => 2000,
                'is_featured' => true,
                'has_premium_badge' => true,
                'search_priority' => 10,
                'max_campaigns' => 20,
                'has_banner_ad' => true,
                'has_sales_reports' => true,
                'has_analytics' => true,
                'report_level' => 'full',
                'order_priority' => 10,
                'can_set_prep_time' => true,
                'custom_shipping_options' => true,
                'has_vendor_delivery' => true,
                'commission_rate' => 2,
                'commission_per_order' => 0.25,
                'activation_fee_waived' => true,
            ],
        ];

        foreach ($packages as $data) {
            VendorPackage::updateOrCreate(
                $data
            );
        }

        $this->command->info('Created ' . count($packages) . ' vendor packages.');
    }
}
