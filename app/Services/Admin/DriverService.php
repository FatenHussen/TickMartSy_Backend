<?php

namespace App\Services\Admin;

use App\Authorization\CityAccess;
use App\Http\Resources\Driver\AllResource;
use App\Http\Resources\Driver\OneResource;

use App\Models\Admin;
use App\Models\Driver;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class DriverService extends BaseService
{
    public function __construct(Driver $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'phone', 'email', 'address', 'status', 'vehicle_type', 'vehicle_name'];
        $this->syncRelations = [
            'cities'  => 'city_ids',
            'shops'   => 'shop_ids',
            'vendors' => 'vendor_ids',
        ];
        $this->relations = ['cities', 'shops', 'vendors'];

        $this->singleImages = ['image', 'vehicle_image'];
    }

    protected function applyAdminCityRestriction(Builder $query): Builder
    {
        $admin = auth('admin')->user();
        if (! $admin instanceof Admin) {
            return $query;
        }

        return CityAccess::for($admin)->constrainDrivers($query);
    }
}
