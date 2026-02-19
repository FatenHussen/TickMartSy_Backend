<?php

namespace App\Services\Admin;

use App\Models\Package;
use App\Services\BaseService;
use App\Http\Resources\Admin\Package\OneResource;
use App\Http\Resources\Admin\Package\AllResource;

class PackageService extends BaseService
{
    protected $model = Package::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'subscriptions',
    ];

    protected $searchableFields = [
        'id',
    ];

    protected $sortableFields = [
        'id',
        'price',
        'duration_days',
        'monthly_orders_limit',
        'free_delivery_count',
        'discount_percentage',
        'points_bonus',
        'created_at',
    ];

    /**
     * Override queryBuilder to add custom filters
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by price range
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Filter by duration
        if (isset($filters['min_duration'])) {
            $query->where('duration_days', '>=', $filters['min_duration']);
        }

        if (isset($filters['max_duration'])) {
            $query->where('duration_days', '<=', $filters['max_duration']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    /**
     * Override create to handle translations
     */
    public function create($data)
    {
        $package = $this->model::create($data);

        // Return fresh resource with relations
        $package = $this->model::with($this->relations)->findOrFail($package->id);
        return new $this->resource($package);
    }

    /**
     * Override update to handle translations
     */
    public function update($id, array $data)
    {
        $package = $this->model::findOrFail($id);

        // Handle translations
        if (isset($data['name'])) {
            $package->setTranslations('name', $data['name']);
            unset($data['name']);
        }

        // Update package
        $package->update($data);

        // Return fresh resource with relations
        $package = $this->model::with($this->relations)->findOrFail($package->id);
        return new $this->resource($package);
    }

    /**
     * Delete package
     */
    public function delete($id): bool
    {
        $package = Package::findOrFail($id);

        // Check if package has active subscriptions
        $activeSubscriptions = $package->subscriptions()
            ->where('status', 'active')
            ->count();

        if ($activeSubscriptions > 0) {
            throw new \Exception('Cannot delete package with active subscriptions');
        }

        $package->delete();

        return true;
    }
}
