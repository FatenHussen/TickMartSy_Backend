<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Vendor;
use App\Models\Shop;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Hash;

class VendorRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'vendor-user';

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Vendor level
            'vendor.dashboard',
            'vendor.reports',
            'vendor.settings',

            // Shop level
            'shop.dashboard',
            'shop.orders.view',
            'shop.orders.manage',
            'shop.inventory.adjust',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $vendorOwner = Role::firstOrCreate([
            'name' => 'vendor-owner',
            'guard_name' => $guard,
        ]);
        $vendorOwner->syncPermissions(Permission::where('guard_name', $guard)->get());

        $vendorAdmin = Role::firstOrCreate([
            'name' => 'vendor-admin',
            'guard_name' => $guard,
        ]);
        $vendorAdmin->syncPermissions([
            'vendor.dashboard',
            'vendor.reports',
            'vendor.settings',
        ]);

        $shopManager = Role::firstOrCreate([
            'name' => 'shop-manager',
            'guard_name' => $guard,
        ]);
        $shopManager->syncPermissions([
            'shop.dashboard',
            'shop.orders.manage',
            'shop.inventory.adjust',
        ]);

        $shopEmployee = Role::firstOrCreate([
            'name' => 'shop-employee',
            'guard_name' => $guard,
        ]);
        $shopEmployee->syncPermissions([
            'shop.dashboard',
            'shop.orders.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vendor
        |--------------------------------------------------------------------------
        */

        $vendor = Vendor::find(1);

        /*
        |--------------------------------------------------------------------------
        | Shops (2 branches)
        |--------------------------------------------------------------------------
        */

        $shop1 = Shop::find(1);

        $shop2 = Shop::find(2);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        $employee = VendorUser::firstOrCreate(
            ['email' => 'employee@vendor.com'],
            [
                'name' => 'Multi Branch Employee',
                'password' => Hash::make('password'),
                'is_active' => true,
                'vendor_id' => $vendor->id,
            ]
        );



        // app(PermissionRegistrar::class)->setPermissionsTeamId($shop1->id);
        $employee->assignRole('shop-manager');

        $employee->unsetRelation('roles')->unsetRelation('permissions');

        // app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $employee->assignRole('shop-employee');

        // app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
