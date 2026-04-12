<?php

namespace App\Models\Concerns;

use App\Models\Admin;
use App\Models\Area;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait AppliesAreaScope
{
    protected static function bootAppliesAreaScope(): void
    {
        static::addGlobalScope('area', function (Builder $builder): void {
            static::applyAreaScope($builder);
        });
    }

    protected static function applyAreaScope(Builder $builder): void
    {
        $admin = static::currentAdmin();
        if (!$admin || $admin->isSuperAdmin()) {
            return;
        }

        $areaIds = $admin->areaIds();
        if ($areaIds->isEmpty()) {
            $builder->whereRaw('0 = 1');
            return;
        }

        $model = $builder->getModel();

        if (static::hasDirectAreaColumn($model)) {
            $builder->whereIn($model->getTable() . '.area_id', $areaIds->all());
            return;
        }

        foreach (static::configuredAreaRelationPaths($model) as $path) {
            if (static::applyRelationAreaFilter($builder, $model, $path, $areaIds)) {
                break;
            }
        }
    }

    protected static function currentAdmin(): ?Admin
    {
        return Auth::guard('admin')->user();
    }

    protected static function hasDirectAreaColumn(Model $model): bool
    {
        return Schema::hasColumn($model->getTable(), 'area_id');
    }

    protected static function configuredAreaRelationPaths(Model $model): array
    {
        $defaults = ['area', 'areas'];

        if (property_exists($model, 'areaRelationPaths')) {
            $paths = (array) $model::$areaRelationPaths;
            return array_unique(array_merge($paths, $defaults));
        }

        return $defaults;
    }

    protected static function applyRelationAreaFilter(Builder $builder, Model $model, string $path, Collection $areaIds): bool
    {
        $relationParts = explode('.', $path);
        $current = $model;
        $applied = [];

        foreach ($relationParts as $relationName) {
            if (!method_exists($current, $relationName)) {
                return false;
            }

            $relation = $current->$relationName();
            if (!$relation instanceof Relation) {
                return false;
            }

            $applied[] = $relationName;
            $current = $relation->getRelated();
        }

        $column = static::resolveAreaColumnForModel($current);
        if (!$column) {
            return false;
        }

        $builder->whereHas(implode('.', $applied), function (Builder $query) use ($current, $column, $areaIds): void {
            $query->whereIn($current->getTable() . '.' . $column, $areaIds->all());
        });

        return true;
    }

    protected static function resolveAreaColumnForModel(Model $model): ?string
    {
        if (Schema::hasColumn($model->getTable(), 'area_id')) {
            return 'area_id';
        }

        if ($model instanceof Area) {
            return $model->getKeyName();
        }

        return null;
    }
}
