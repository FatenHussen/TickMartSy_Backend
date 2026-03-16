<?php

namespace App\Services\Admin;

use App\Models\VendorPackage;
use App\Services\BaseService;
use App\Http\Resources\Admin\VendorPackage\AllResource;
use App\Http\Resources\Admin\VendorPackage\OneResource;
use App\Exceptions\CustomExceptionWithMessage;

class VendorPackageService extends BaseService
{
    protected $model = VendorPackage::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;
    protected $relations = ['subscriptions'];
    protected $searchableFields = ['id', 'slug'];
    protected $sortableFields = ['id', 'price', 'duration_days', 'max_products','created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        if (isset($filters['slug'])) {
            $query->where('slug', $filters['slug']);
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        return parent::queryBuilder($query, $filters, $config);
    }

    public function create($data)
    {
        $package = $this->model::create($data);
        return new $this->resource($package->load($this->relations));
    }

    public function update($id, array $data)
    {
        $package = $this->model::findOrFail($id);
        if (isset($data['name'])) {
            $package->setTranslations('name', $data['name']);
            unset($data['name']);
        }
        if (isset($data['description'])) {
            $package->setTranslations('description', $data['description'] ?? []);
            unset($data['description']);
        }
        $package->update($data);
        return new $this->resource($package->fresh($this->relations));
    }

    public function delete($id): bool
    {
        $package = VendorPackage::findOrFail($id);
        if ($package->subscriptions()->where('status', 'active')->exists()) {
            throw new CustomExceptionWithMessage('custom.packages.cannot_delete_with_active_subscriptions');
        }
        $package->delete();
        return true;
    }
}
