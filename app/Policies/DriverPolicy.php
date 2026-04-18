<?php

namespace App\Policies;

use App\Authorization\CityAccess;
use App\Models\Admin;
use App\Models\Driver;

class DriverPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Driver $driver): bool
    {
        return CityAccess::for($admin)->canAccessDriver($driver);
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Driver $driver): bool
    {
        return $this->view($admin, $driver);
    }

    public function delete(Admin $admin, Driver $driver): bool
    {
        return $this->view($admin, $driver);
    }
}
