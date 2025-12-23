<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait ResourcePermissions
{
    protected static string $guardName = 'store-user';

    protected static function checkPermission(string $action): bool
    {
        Log::info("message1");
        $user = Auth::user('store-user');

        if (! $user) {
            return false;
        }
        Log::info("message2");

        $modelName = strtolower(class_basename(static::$model));
        $permissionName = "{$modelName}.{$action}";
        $guard = static::$guardName;

        try {
            Log::info("message3");

            return $user->hasPermissionTo($permissionName, $guard);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::checkPermission('view');
    }

    public static function canViewAny(): bool
    {
        return static::checkPermission('view');
    }

    public static function canCreate(): bool
    {
        return static::checkPermission('create');
    }

    public static function canEdit($record): bool
    {
        return static::checkPermission('update');
    }

    public static function canDelete($record): bool
    {
        return static::checkPermission('delete');
    }
}
