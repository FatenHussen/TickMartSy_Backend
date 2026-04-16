<?php

namespace App\Services\Shared;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder;

class DriverCoverageService
{
    public function loadDriverWithCoverage(int $driverId): Driver
    {
        return Driver::query()
            ->with([
                'cities.areas',
                'shops',
                'vendors.shops',
            ])
            ->findOrFail($driverId);
    }

    public function applyInstantOrderCoverage(Builder $query, Driver $driver): void
    {
        $cityAreaIds = $driver->cities->flatMap->areas->pluck('id')->filter()->unique()->values();
        $driverShopIds = $driver->shops->pluck('id')->unique()->values();
        $driverVendorIds = $driver->vendors->pluck('id')->unique()->values();
        $hasCityFilter = $cityAreaIds->isNotEmpty();
        $hasShopFilter = $driverShopIds->isNotEmpty();
        $hasVendorFilter = $driverVendorIds->isNotEmpty();

        // Each dimension is optional:
        // - no cities linked => no city restriction
        // - no shops linked => no shop restriction
        // - no vendors linked => no vendor restriction
        $deliveryAreaIds = collect();
        if ($hasCityFilter) {
            $deliveryAreaIds = $deliveryAreaIds->merge($cityAreaIds);
        }
        if ($hasShopFilter) {
            $deliveryAreaIds = $deliveryAreaIds->merge($driver->shops->pluck('area_id')->filter());
        }
        if ($hasVendorFilter) {
            $deliveryAreaIds = $deliveryAreaIds->merge(
                $driver->vendors->flatMap(static fn ($v) => $v->shops->pluck('area_id'))->filter()
            );
        }
        $deliveryAreaIds = $deliveryAreaIds->unique()->values();

        // Apply delivery-area filter only if any dimension is actually constrained.
        if ($deliveryAreaIds->isNotEmpty()) {
            $query->whereHas('address', function (Builder $q) use ($deliveryAreaIds): void {
                $q->whereIn('area_id', $deliveryAreaIds->all());
            });
        }

        // No filters at all => full coverage.
        if (! $hasShopFilter && ! $hasVendorFilter && ! $hasCityFilter) {
            return;
        }

        $query->whereDoesntHave('items.shopProductVariant.shop', function (Builder $q) use (
            $driverShopIds,
            $driverVendorIds,
            $cityAreaIds,
            $hasShopFilter,
            $hasVendorFilter,
            $hasCityFilter
        ): void {
            $q->where(function (Builder $w) use (
                $driverShopIds,
                $driverVendorIds,
                $cityAreaIds,
                $hasShopFilter,
                $hasVendorFilter,
                $hasCityFilter
            ): void {
                if ($hasShopFilter) {
                    $w->whereNotIn('shops.id', $driverShopIds->all());
                }

                if ($hasVendorFilter) {
                    $w->where(function (Builder $v) use ($driverVendorIds): void {
                        $v->whereNull('shops.vendor_id')
                            ->orWhereNotIn('shops.vendor_id', $driverVendorIds->all());
                    });
                }

                if ($hasCityFilter) {
                    $w->whereNotIn('shops.area_id', $cityAreaIds->all());
                }
            });
        });
    }
}
