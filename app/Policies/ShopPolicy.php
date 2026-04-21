<?php

namespace App\Policies;

use App\Authorization\CityAccess;
use App\Models\Admin;
use App\Models\Shop;
use App\Models\VendorUser;

class ShopPolicy
{
    public function viewAny(Admin|VendorUser $user): bool
    {
        if ($user instanceof VendorUser) {
            return $user->shops()->exists();
        }

        return true;
    }

    public function view(Admin|VendorUser $user, Shop $shop): bool
    {
        if ($user instanceof VendorUser) {
            return $user->shops()->whereKey($shop->getKey())->exists();
        }

        return CityAccess::for($user)->canAccessShop($shop);
    }

    public function create(Admin|VendorUser $user): bool
    {
        if ($user instanceof VendorUser) {
            return false;
        }

        return true;
    }

    public function update(Admin|VendorUser $user, Shop $shop): bool
    {
        return $this->view($user, $shop);
    }

    public function delete(Admin|VendorUser $user, Shop $shop): bool
    {
        if ($user instanceof VendorUser) {
            return false;
        }

        return $this->view($user, $shop);
    }
}
