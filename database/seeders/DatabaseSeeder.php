<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with real system data only.
     * Dummy catalog, users, orders, and demo content are not seeded.
     */
    public function run(): void
    {
        $this->call([
            // Geography
            GovernorateSeeder::class,
            CountrySeeder::class,
            SaleCountrySeeder::class,

            // Admin & permissions
            AdminSeeder::class,
            AdminRolePermissionSeeder::class,
            RolePermissionSeeder::class,

            // Languages, currencies, lookups
            LanguageSeeder::class,
            CurrencySeeder::class,
            UnitSeeder::class,
            ColorSeeder::class,

            // Pages (empty shells — no demo banners/products)
            DisplayTypeSeeder::class,
            PageSeeder::class,
            CategoryDetailsPageSeeder::class,

            // App settings (after pages so home page id can be linked)
            SettingSeeder::class,
            SystemSettingSeeder::class,
            NavMenuSeeder::class,
            QuickActionSeeder::class,

            // Payments, points config, legal
            PaymentMethodSeeder::class,
            PointRuleSeeder::class,
            ExchangeSettingsSeeder::class,
            LegalDocumentSeeder::class,
            DriverContactMethodSeeder::class,
            IconSeeder::class,

            // Required for site product create (sale_channel=platform)
            PlatformVendorSeeder::class,
        ]);
    }
}
