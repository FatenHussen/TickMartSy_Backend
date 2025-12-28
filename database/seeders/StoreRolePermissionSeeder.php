<?php

namespace Database\Seeders;

use App\Models\StoreUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StoreRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $storeModels = [
            'StoreUser',
            'Product',
            'Order',
            'Item',
            'Category',
            'Role',
            'Permission',
            'Language'
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($storeModels as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => strtolower($model) . '.' . $action,
                    'guard_name' => 'store-user',
                ]);
            }
        }

        $storeAdmin = Role::firstOrCreate([
            'name' => 'store-admin',
            'guard_name' => 'store-user',
        ]);

        $storeEmployee = Role::firstOrCreate([
            'name' => 'store-employee',
            'guard_name' => 'store-user',
        ]);

        $storeAdmin->syncPermissions(Permission::where('guard_name', 'store-user')->get());

        $storeEmployee->syncPermissions(
            Permission::where('guard_name', 'store-user')
                ->whereNotIn('name', [
                    'storeuser.view',
                    'storeuser.create',
                    'storeuser.delete',
                    'storeuser.update',
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

        $adminUser = StoreUser::firstOrCreate(
            ['email' => 'storeadmin@store.com'],
            [
                'name' => 'Store Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'store_id' => 1,
            ]
        );
        $adminUser->assignRole($storeAdmin);
        $EmployeeUser = StoreUser::firstOrCreate(
            ['email' => 'storeemployee@store.com'],
            [
                'name' => 'Store Employee',
                'password' => bcrypt('password'),
                'is_active' => true,
                'store_id' => 1,
            ]
        );
        $EmployeeUser->assignRole($storeEmployee);
    }
}
