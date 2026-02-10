<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GovernorateSeeder::class,
            AdminRolePermissionSeeder::class,
            LanguageSeeder::class,
            RolePermissionSeeder::class,
            // RolePermissionSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            VendorSeeder::class,
            ShopSeeder::class,
            ShopUserSeeder::class,
            CategoryAttributeSeeder::class,
            AttributeValueSeeder::class,
            CategoryDetailSeeder::class,
            ProductSeeder::class,
            ProductCategoryDetailSeeder::class,
            ProductExtraDetailSeeder::class,
            ProductVariantSeeder::class,
            ProductMediaSeeder::class,
            ShopProductVariantSeeder::class,
            PageSectionSeeder::class,
            ServiceSeeder::class,
            VendorRolePermissionSeeder::class,
            BadgeSeeder::class,
            RecipeSeeder::class,
            BasketSeeder::class,
            BasketItemSeeder::class,
            // UserSeeder::class,
            // BasketItemCompanySeeder::class,
            BasketScheduleSeeder::class,
            ScheduleSeeder::class,
            UserBasketScheduleSeeder::class,
            UserBasketScheduleItemSeeder::class,
            CouponSeeder::class,

            // Point System Seeders
            PointRuleSeeder::class,
            ExchangeSettingsSeeder::class,
            GiftSeeder::class,
            PaymentMethodSeeder::class,
            DriverSeeder::class,
            RatingSeeder::class,
            UserSeeder::class,
            UserAddressSeeder::class,
            // DriverSeeder::class,
            OrderWithItemsSeeder::class,
        ]);
        User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
            'area_id' => 1
        ]);
    }
}
