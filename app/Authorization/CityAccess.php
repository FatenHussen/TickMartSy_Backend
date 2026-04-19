<?php

namespace App\Authorization;

use App\Models\Admin;
use App\Models\Area;
use App\Models\Driver;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

/**
 * Data-level authorization: restricts admin data by city intersection.
 * Empty city assignment on the acting admin ⇒ full access (except super-admin shortcut is not required for full access).
 * Super-admin always has full access.
 */
final class CityAccess
{
    /**
     * @param  list<int>  $cityIds
     */
    private function __construct(
        private readonly Admin $admin,
        private readonly array $cityIds,
        private readonly bool $fullAccess,
    ) {}

    public static function for(Admin $admin): self
    {
        $cityIds = $admin->relationLoaded('cities')
            ? $admin->cities->pluck('id')->all()
            : $admin->cities()->pluck('cities.id')->all();

        $cityIds = array_values(array_unique(array_map(static fn ($id) => (int) $id, $cityIds)));

        $fullAccess = $admin->isSuperAdmin() || $cityIds === [];

        return new self($admin, $cityIds, $fullAccess);
    }

    public function hasFullAccess(): bool
    {
        return $this->fullAccess;
    }

    /**
     * @return list<int>
     */
    public function cityIds(): array
    {
        return $this->cityIds;
    }

    public function constrainDrivers(Builder $query): Builder
    {
        if ($this->fullAccess) {
            return $query;
        }

        return $query->whereHas('cities', function (Builder $q): void {
            $q->whereIn('cities.id', $this->cityIds);
        });
    }

    public function constrainAdmins(Builder $query): Builder
    {
        if ($this->fullAccess) {
            return $query;
        }

        return $query->whereHas('cities', function (Builder $q): void {
            $q->whereIn('cities.id', $this->cityIds);
        });
    }

    public function constrainShops(Builder $query): Builder
    {
        if ($this->fullAccess) {
            return $query;
        }

        return $query->whereHas('area', function (Builder $q): void {
            $q->whereIn('city_id', $this->cityIds);
        });
    }

    /**
     * Restrict a query that belongs to a driver (e.g. wallet transactions).
     */
    public function constrainViaDriverRelation(Builder $query, string $driverRelation = 'driver'): Builder
    {
        if ($this->fullAccess) {
            return $query;
        }

        return $query->whereHas($driverRelation, function (Builder $driverQuery): void {
            $this->constrainDrivers($driverQuery);
        });
    }

    public function canAccessDriver(Driver $driver): bool
    {
        if ($this->fullAccess) {
            return true;
        }

        return $driver->cities()->whereIn('cities.id', $this->cityIds)->exists();
    }

    public function canAccessAdmin(Admin $target): bool
    {
        if ($this->fullAccess) {
            return true;
        }

        return $target->cities()->whereIn('cities.id', $this->cityIds)->exists();
    }

    public function canAccessShop(Shop $shop): bool
    {
        if ($this->fullAccess) {
            return true;
        }

        if (! $shop->area_id) {
            return false;
        }

        $cityId = $shop->relationLoaded('area')
            ? $shop->area?->city_id
            : Area::whereKey($shop->area_id)->value('city_id');

        return $cityId && in_array((int) $cityId, $this->cityIds, true);
    }

    public function canAssignArea(int $areaId): bool
    {
        if ($this->fullAccess) {
            return true;
        }

        $cityId = Area::whereKey($areaId)->value('city_id');

        return $cityId && in_array((int) $cityId, $this->cityIds, true);
    }

    /**
     * @param  list<int>  $cityIds  Normalized city primary keys
     */
    public function allCityIdsAreAssignable(array $cityIds): bool
    {
        if ($this->fullAccess) {
            return true;
        }

        foreach ($cityIds as $id) {
            if (! $id || ! in_array((int) $id, $this->cityIds, true)) {
                return false;
            }
        }

        return true;
    }
}
