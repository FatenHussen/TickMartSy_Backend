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
        'schedules',
        'favorites'
    ];
    protected $searchableFields = ['name'];
    protected $sortableFields   = ['id', 'created_at', 'num_sold', 'rating'];
    protected $pagination = true;

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Extract type filter before passing to parent
        $type = $filters['type'] ?? null;
        unset($filters['type']);
        $sort= $filters['sort_by'] ?? null;
        unset($filters['sort_by']);

        // Apply base query builder first (search, sort, favorites)
        $query = parent::queryBuilder($query, $filters, $config);

        // Apply latest ordering by default
        if (empty($config['sortField'])) {
            $query->latest();
        }

        // Filter by schedule status
        if (isset($filters['is_schedule'])) {
            $query->where('is_schedule', $filters['is_schedule']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Price range filter - تحويل من عملة اليوزر للدولار
        $query = $this->applyPriceFilter($query, $filters);

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
        if (!empty($type)) {
            $this->applyTypeFilters($query, $type);
        }

        if (!empty($sort)) {
            $this->applySortBy($query, $sort);
        }

        return $query;
    }
    public function query(array $filters)
    {
        $query = Basket::query()->latest();


        $query->where('is_schedule', 0);

        /* ================= FAVORITES ================= */
        if (
            auth('user')->check() &&
            method_exists($query->getModel(), 'favorites')
        ) {
            $query->withExists([
                'favorites as is_favorite' => function ($q) {
                    $q->where('user_id', auth('user')->id());
                }
            ]);
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

    protected function applySortBy($query, string $sortBy): void
    {
        $query->reorder();

        match ($sortBy) {
            'price_desc' => $query->orderBy('price', 'desc'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => null,
        };
    }
}
