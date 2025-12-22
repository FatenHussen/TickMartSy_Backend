<?php

namespace App\Services\Admin;

use App\Models\Store;

class StoreService
{
    public function list()
    {
        return Store::latest()->paginate(10);
    }

    public function create(array $data): Store
    {
        return Store::create($data);
    }

    public function update(Store $store, array $data): Store
    {
        $store->update($data);
        return $store;
    }

    public function delete(Store $store): void
    {
        $store->delete();
    }
}
