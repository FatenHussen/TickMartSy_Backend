<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Brand\AllResource;
use App\Http\Resources\Admin\Brand\OneResource;
use App\Services\BaseService;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandService extends BaseService
{
    public function __construct(Brand $model)
    {
        $this->model        = $model;
        $this->resource     = OneResource::class;
        $this->collection   = AllResource::class;
        $this->singleImages = ['image'];
        $this->pagination   = true;
        $this->relations    = ['governorate', 'city', 'category', 'originCountry'];
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'created_at', 'is_active', 'category_id', 'origin_country_id', 'order'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['name'])) {
            $search = strtolower(trim((string) $filters['name']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
            unset($filters['name']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
            unset($filters['is_active']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
            unset($filters['category_id']);
        }

        if (!empty($filters['origin_country_id'])) {
            $query->where('origin_country_id', $filters['origin_country_id']);
            unset($filters['origin_country_id']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    public function reorder(array $orderedIds): int
    {
        return DB::transaction(function () use ($orderedIds) {
            $existingIds = Brand::query()->whereIn('id', $orderedIds)->pluck('id')->all();

            if (count($existingIds) !== count($orderedIds)) {
                abort(422, 'Some brands are invalid.');
            }

            $updated = 0;
            foreach ($orderedIds as $index => $id) {
                $updated += Brand::query()
                    ->where('id', $id)
                    ->update(['order' => $index + 1]);
            }

            return $updated;
        });
    }
}
