<?php

namespace App\Services\User;

use App\Http\Resources\Basket\AllResource;
use App\Http\Resources\Basket\OneResource;
use App\Models\Basket;
use App\Services\BaseService;

class BasketService extends BaseService
{
    protected $model      = Basket::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'items',
        'items.product',
        'items.variant',
        // 'items.companies',
        // 'items.companies.brand',
        'schedules',
    ];
    protected $searchableFields = ['name'];
    protected $sortableFields   = ['id'];
    protected $pagination = true;

    public function query(array $filters)
    {
        $query = Basket::query()->latest();

        // Filter by schedule status
        if (isset($filters['is_schedule'])) {
            $query->where('is_schedule', $filters['is_schedule']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Price range filter
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $query->where(function ($q) use ($filters) {
                if (!empty($filters['price_min'])) {
                    $q->where('price', '>=', $filters['price_min']);
                }
                if (!empty($filters['price_max'])) {
                    $q->where('price', '<=', $filters['price_max']);
                }
            });
        }

        // Rating filter
        if (!empty($filters['rating_min'])) {
            $query->where('rating', '>=', $filters['rating_min']);
        }

        // Items count filter
        if (!empty($filters['items_count_min']) || !empty($filters['items_count_max'])) {
            $query->whereHas('items', function ($q) {}, '>=', $filters['items_count_min'] ?? 0);

            if (!empty($filters['items_count_max'])) {
                $query->whereHas('items', function ($q) {}, '<=', $filters['items_count_max']);
            }
        }

        // Type filters
        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters['type']);
        }

        return $query;
    }

    protected function applyTypeFilters($query, $type)
    {
        switch ($type) {
            case 'new':
                $query->orderBy('created_at', 'desc');
                break;

            case 'best_selling':
                $query->orderBy('num_sold', 'desc');
                break;

            case 'top_rated':
                $query->orderBy('rating', 'desc');
                break;
        }
    }
}
