<?php

namespace App\Http\Requests\Admin\Product;

use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;

trait ResolvesProductVendorId
{
    /**
     * Bind vendor_id to a real vendors.id before exists:vendors,id runs.
     *
     * Admin create used to inject 1 (or vendor_users.id) even when that row
     * is missing — Laravel then returns «حقل vendor id غير موجود».
     */
    protected function resolveProductVendorId(bool $defaultChannelToPlatform = true): void
    {
        if (auth('vendor-user')->check()) {
            $vendorUser = auth('vendor-user')->user();
            $this->merge([
                'sale_channel' => 'shop',
                'vendor_id' => $vendorUser?->vendor_id,
            ]);

            return;
        }

        if (!$defaultChannelToPlatform && !$this->filled('sale_channel')) {
            return;
        }

        $channel = $this->input('sale_channel', $defaultChannelToPlatform ? 'platform' : null);
        if (!in_array($channel, ['platform', 'shop'], true)) {
            $channel = 'platform';
        }

        $merge = ['sale_channel' => $channel];

        if ($channel === 'platform') {
            $merge['vendor_id'] = ProductService::resolvePlatformVendorId();
        } else {
            $merge['vendor_id'] = $this->vendorIdFromShopVariants()
                ?? $this->validExistingVendorId($this->input('vendor_id'));
        }

        $this->merge($merge);
    }

    private function vendorIdFromShopVariants(): ?int
    {
        $shopVariants = $this->input('shop_variants');
        if (!is_array($shopVariants)) {
            return null;
        }

        $firstShopId = collect($shopVariants)->pluck('shop_id')->filter()->first();
        if (!$firstShopId) {
            return null;
        }

        $vendorId = Shop::query()->whereKey($firstShopId)->value('vendor_id');

        return $vendorId ? (int) $vendorId : null;
    }

    private function validExistingVendorId(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value) || (int) $value <= 0) {
            return null;
        }

        $id = (int) $value;

        return Vendor::query()->whereKey($id)->exists() ? $id : null;
    }
}
