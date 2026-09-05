<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $names = [
            'warranty.view',
            'warranty.create',
            'warranty.update',
            'warranty.delete',
        ];

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'admin');
        }

        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->whereIn('name', $names)
            ->get();

        foreach (['admin', 'employee'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'admin')
                ->first();

            $role?->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $names = [
            'warranty.view',
            'warranty.create',
            'warranty.update',
            'warranty.delete',
        ];

        Permission::query()
            ->where('guard_name', 'admin')
            ->whereIn('name', $names)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
