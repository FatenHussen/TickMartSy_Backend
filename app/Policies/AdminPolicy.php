<?php

namespace App\Policies;

use App\Authorization\CityAccess;
use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Admin $target): bool
    {
        return CityAccess::for($admin)->canAccessAdmin($target);
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return $this->view($admin, $target);
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        return $this->view($admin, $target);
    }
}
