<?php

namespace Database\Seeders;

use App\Models\VendorUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class VendorRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $vendorModels = [
            'VendorUser',
            'Product',
            'Order',
            'Item',
            'Category',
            'Role',
            'Permission',
            'Language'
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($vendorModels as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => strtolower($model) . '.' . $action,
                    'guard_name' => 'vendor-user',
                ]);
            }
        }

        $vendorAdmin = Role::firstOrCreate([
            'name' => 'vendor-admin',
            'guard_name' => 'vendor-user',
        ]);

        $vendorEmployee = Role::firstOrCreate([
            'name' => 'vendor-employee',
            'guard_name' => 'vendor-user',
        ]);

        $vendorAdmin->syncPermissions(Permission::where('guard_name', 'vendor-user')->get());

        $vendorEmployee->syncPermissions(
            Permission::where('guard_name', 'vendor-user')
                ->whereNotIn('name', [
                    'vendoruser.view',
                    'vendoruser.create',
                    'vendoruser.delete',
                    'vendoruser.update',
                    'role.create',
                    'role.view',
                    'role.update',
                    'role.create',
                    'role.delete',
                    'role.create',
                    'permission.view',
                    'permission.update',
                    'permission.create',
                    'permission.delete',
                ])->get()
        );

        $adminUser = VendorUser::firstOrCreate(
            ['email' => 'vendoradmin@vendor.com'],
            [
                'name' => 'vendor Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'vendor_id' => 1,
            ]
        );
        $adminUser->assignRole($vendorAdmin);
        $EmployeeUser = VendorUser::firstOrCreate(
            ['email' => 'vendoremployee@vendor.com'],
            [
                'name' => 'vendor Employee',
                'password' => bcrypt('password'),
                'is_active' => true,
                'vendor_id' => 1,
            ]
        );
        $EmployeeUser->assignRole($vendorEmployee);
    }
}
