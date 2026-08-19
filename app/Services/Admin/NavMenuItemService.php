<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\NavMenuItem\AllResource;
use App\Http\Resources\Admin\NavMenuItem\OneResource;
use App\Models\NavMenuItem;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class NavMenuItemService extends BaseService
{
    public function __construct(NavMenuItem $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->singleImages = ['icon'];
        $this->relations = ['page', 'category', 'brand'];
        $this->pagination = false;
        $this->searchableFields = ['id', 'type', 'route_key'];
        $this->sortableFields = ['id', 'order', 'created_at'];
    }

    /**
     * Persist the drag & drop order coming from the dashboard.
     */
    public function reorder(array $orderedIds): int
    {
        return DB::transaction(function () use ($orderedIds) {
            $existing = NavMenuItem::query()->whereIn('id', $orderedIds)->pluck('id')->all();

            if (count($existing) !== count($orderedIds)) {
                abort(422, 'Some menu items do not exist.');
            }

            $updated = 0;
            foreach ($orderedIds as $index => $id) {
                $updated += NavMenuItem::query()
                    ->where('id', $id)
                    ->update(['order' => $index + 1]);
            }

            return $updated;
        });
    }
}
