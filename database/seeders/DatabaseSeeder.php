<?php

namespace Database\Seeders;

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
            // Basic Setup
            BadgeSeeder::class,
            GovernorateSeeder::class,
            CountrySeeder::class, // بلدان المنشأ (Origin Countries)
            SaleCountrySeeder::class, // بلدان المبيع (Sale Countries)
            // CitySeeder::class,
            // AreaSeeder::class,
            // Admin & Permissions
            AdminSeeder::class,
            AdminRolePermissionSeeder::class,
            RolePermissionSeeder::class,

            // Languages & Settings
            LanguageSeeder::class,
            SettingSeeder::class,
            SystemSettingSeeder::class,
            CurrencySeeder::class,

            // Categories & Brands
            CategorySeeder::class,
            BrandSeeder::class,
            ColorSeeder::class, // جدول الألوان - يجب أن يكون قبل CategoryAttributeSeeder
            CategoryAttributeSeeder::class,
            AttributeValueSeeder::class,
            CategoryDetailSeeder::class,

            // Vendors & Shops
            VendorSeeder::class,
            ShopSeeder::class,
            VendorUserSeeder::class,
            ShopUserSeeder::class,

            // Seller Registrations
            SellerRegistrationSeeder::class,

            // Products
            ProductSeeder::class,
            ProductCategoryDetailSeeder::class,
            ProductExtraDetailSeeder::class,
            ProductVariantSeeder::class,
            ProductMediaSeeder::class,
            ShopProductVariantSeeder::class,

            // Pages & Services
            PageSectionSeeder::class,
            ServiceSeeder::class,
            RestaurantAndServiceProviderSeeder::class,
            VendorServiceCatalogSeeder::class,
            QuickActionSeeder::class,
            VendorServiceSeeder::class,

            // Recipes & Baskets
            RecipeSeeder::class,
            BasketSeeder::class,
            BasketItemSeeder::class,
            BasketScheduleSeeder::class,

            // Users
            UserSeeder::class,
            UserAddressSeeder::class,
            // UserTokenSeeder::class, // FCM tokens for push notifications

            // Schedules
            ScheduleSeeder::class,
            UserBasketScheduleSeeder::class,
            UserBasketScheduleItemSeeder::class,

            // Coupons & Packages
            CouponSeeder::class,
            PackageSeeder::class,
            SubscriptionSeeder::class,

            // Vendor Packages & Subscriptions
            VendorPackageSeeder::class,
            VendorSubscriptionSeeder::class,

            // Point System (GiftSeeder أولاً - مطلوب لاستبدال النقاط بهدية)
            GiftSeeder::class,
            PointSystemSeeder::class, // يشمل PointTransactionSeeder
            ExchangeSettingsSeeder::class,
            PointExchangeSeeder::class, // لازم يجي بعد PointSystemSeeder
            UserGiftSeeder::class, // User gifts redemptions

            // Payment & Orders
            PaymentMethodSeeder::class,
            DriverSeeder::class,
            OrderWithItemsSeeder::class,
            ServiceOrderSeeder::class,
            UserBasketsAndOrdersSeeder::class,
            DriverWalletTransactionSeeder::class,

            // Ratings & Reviews
            RatingSeeder::class,

            // Complaints
            ComplaintSeeder::class,
            LeenUserSeeder::class,

            // Legal & FAQ
            LegalDocumentSeeder::class,
            FaqSeeder::class,
            IconSeeder::class,

            PromotionSeeder::class,
            FlashSaleSeeder::class,
            PopupCampaignSeeder::class

        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
            'area_id' => 1
        ]);
    }
}
