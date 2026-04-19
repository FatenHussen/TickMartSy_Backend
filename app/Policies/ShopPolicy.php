<?php

namespace App\Policies;

use App\Authorization\CityAccess;
use App\Models\Admin;
use App\Models\Shop;

class ShopPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Shop $shop): bool
    {
        return CityAccess::for($admin)->canAccessShop($shop);
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Shop $shop): bool
    {
        return $this->view($admin, $shop);
    }

    public function delete(Admin $admin, Shop $shop): bool
    {
        return $this->view($admin, $shop);
    }
}
