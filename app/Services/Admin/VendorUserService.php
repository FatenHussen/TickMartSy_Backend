<?php

namespace App\Services\Admin;

use App\Http\Resources\VendorUser\AllResource;
use App\Http\Resources\VendorUser\OneResource;
use App\Models\VendorUser;
use App\Services\BaseService;
use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Facades\Hash;

class VendorUserService extends BaseService
{
    public function __construct(VendorUser $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['vendor', 'shops'];
        $this->searchableFields = ['name', 'email'];
        $this->syncRelations = [
            'shops' => 'shop_ids'
        ];
    }

    /**
     * Override create to hash password and sync shops
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Extract shop_ids before creating
        $shopIds = $data['shop_ids'] ?? [];
        unset($data['shop_ids']);

        // Create vendor user
        $vendorUser = $this->model->create($data);

        // Sync shops
        if (!empty($shopIds)) {
            // Validate that all shops belong to the same vendor
                $shops = \App\Models\Shop::whereIn('id', $shopIds)->get();
                foreach ($shops as $shop) {
                    if ($shop->vendor_id != $data['vendor_id']) {
                        throw new CustomExceptionWithMessage(
                            'custom.vendors.shops_must_belong_same_vendor',
                            422
                        );
                    }
                }
            $vendorUser->shops()->sync($shopIds);
        }

        return new $this->resource($vendorUser->load($this->relations));
    }

    /**
     * Override update to hash password if changed and sync shops
     */
    public function update($id, array $data)
    {
        $vendorUser = $this->model->findOrFail($id);

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Extract shop_ids before updating
        $shopIds = $data['shop_ids'] ?? null;
        unset($data['shop_ids']);

        // Update vendor user
        $vendorUser->update($data);

        // Sync shops if provided
        if ($shopIds !== null) {
            // Validate that all shops belong to the same vendor
            if (!empty($shopIds)) {
                $shops = \App\Models\Shop::whereIn('id', $shopIds)->get();
                foreach ($shops as $shop) {
                    if ($shop->vendor_id != $vendorUser->vendor_id) {
                        throw new CustomExceptionWithMessage(
                            'custom.vendors.shops_must_belong_same_vendor',
                            422
                        );
                    }
                }
            }
            $vendorUser->shops()->sync($shopIds);
        }

        return new $this->resource($vendorUser->load($this->relations));
    }
}
