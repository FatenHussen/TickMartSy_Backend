<?php

namespace App\Services\Admin;

use App\Exceptions\DeleteConfirmationRequiredException;
use App\Http\Resources\Admin\Category\OneResource;
use App\Models\Category;
use App\Services\Base\CategoryDeleteImpactService;
use App\Services\BaseService;
use App\Http\Resources\Admin\Category\AllResource;
use Illuminate\Support\Facades\DB;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->singleImages = ['icon'];
        $this->relations = ['parent', 'children', 'page'];
        $this->pagination = true;
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'created_at', 'order'];
    }

    public function deleteImpact($id): array
    {
        $category = $this->applyAdminCityRestriction($this->model::query())->findOrFail($id);

        return (new CategoryDeleteImpactService())->impact($category);
    }

    public function linkedItems($id, int $page = 1, int $perPage = 10): array
    {
        $category = $this->applyAdminCityRestriction($this->model::query())->findOrFail($id);

        return (new CategoryDeleteImpactService())->linkedItems($category, $page, $perPage);
    }

    /**
     * الحذف مسموح مع تنبيه بما هو مرتبط.
     * بدون confirm وفي وجود ارتباطات → 409 مع تفاصيل التأثير.
     */
    public function deleteWithConfirmation($id, bool $confirmed = false): array
    {
        $category = $this->applyAdminCityRestriction($this->model::query())->findOrFail($id);
        $impactService = new CategoryDeleteImpactService();
        $impact = $impactService->impact($category);

        if ($impact['requires_confirmation'] && !$confirmed) {
            throw new DeleteConfirmationRequiredException(
                $impact,
                'custom.category_delete_impact.requires_confirmation'
            );
        }

        return DB::transaction(function () use ($category, $impactService) {
            $subtreeIds = $category->idsInSubtree();

            foreach ($subtreeIds as $categoryId) {
                $node = Category::query()->find($categoryId);
                if ($node) {
                    $this->deleteSingleImages($node);
                }
            }

            return $impactService->executeDelete($category);
        });
    }

    public function delete($id): bool
    {
        $this->deleteWithConfirmation($id, request()->boolean('confirm'));

        return true;
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

        if (array_key_exists('is_restaurant', $filters) && $filters['is_restaurant'] !== null) {
            $query->where('is_restaurant', (bool) $filters['is_restaurant']);
            unset($filters['is_restaurant']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    public function reorder(array $orderedIds, ?int $parentId = null): int
    {
        return DB::transaction(function () use ($orderedIds, $parentId) {
            $query = Category::query()->whereIn('id', $orderedIds);

            if ($parentId !== null) {
                $query->where('parent_id', $parentId);
            }

            $existingIds = $query->pluck('id')->all();

            if (count($existingIds) !== count($orderedIds)) {
                abort(422, 'Some categories do not match the requested parent scope.');
            }

            $updated = 0;
            foreach ($orderedIds as $index => $id) {
                $updated += Category::query()
                    ->where('id', $id)
                    ->update(['order' => $index + 1]);
            }

            return $updated;
        });
    }
}
