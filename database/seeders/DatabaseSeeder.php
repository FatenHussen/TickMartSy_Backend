<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            RolePermissionSeeder::class,
            CategorySeeder::class,
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
            ServiceSeeder::class
        ]);
    }
}
