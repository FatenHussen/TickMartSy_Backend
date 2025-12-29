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
            CitySeeder::class,
            AdminRolePermissionSeeder::class,
            RolePermissionSeeder::class,
            CategorySeeder::class,
            LanguageSeeder::class,
            AreaSeeder::class,
            BrandSeeder::class,
            VendorSeeder::class,
            VendorUserSeeder::class,
            VendorRolePermissionSeeder::class,
            ShopSeeder::class,
            ShopUserSeeder::class,
        ]);
    }
}
