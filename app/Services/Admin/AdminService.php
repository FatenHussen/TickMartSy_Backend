<?php

namespace App\Services\Admin;

use App\Authorization\CityAccess;
use App\Http\Resources\Admin\AllResource;
use App\Http\Resources\Admin\OneResource;
use App\Models\Admin;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminService extends BaseService
{

    public function __construct(Admin $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'phone', 'email'];
        $this->relations = ['roles', 'cities'];
        $this->syncRelations = [
            'roles'   => 'roles',
            'cities'  => 'city_ids',
        ];
    }

    public function create($data)
    {
        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);

        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        $object->refresh();
        $object->loadMissing($this->relations);

        return new $this->resource($object) ?? true;
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();

        $object = $this->applyAdminCityRestriction($this->model::query())->findOrFail($id);
        if (property_exists($object, 'translatable')) {
            foreach ($object->translatable as $field) {
                if (isset($data[$field])) {
                    $object->setTranslations($field, $data[$field]);
                    unset($data[$field]);
                }
            }
        }
        $object->update($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        $object->refresh();
        $object->loadMissing($this->relations);

        DB::commit();

        return new $this->resource($object);
    }

    protected function applyAdminCityRestriction(Builder $query): Builder
    {
        $admin = auth('admin')->user();
        if (! $admin instanceof Admin) {
            return $query;
        }

        return CityAccess::for($admin)->constrainAdmins($query);
    }
}
