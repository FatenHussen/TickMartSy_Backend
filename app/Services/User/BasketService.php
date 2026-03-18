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
        // Extract custom filters before passing to parent
        $type = $filters['type'] ?? null;
        $sort = $filters['sort_by'] ?? null;
        $isSchedule = $filters['is_schedule'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $priceMin = $filters['price_min'] ?? null;
        $priceMax = $filters['price_max'] ?? null;
        $ratingMin = $filters['rating_min'] ?? null;
        $itemsCountMin = $filters['items_count_min'] ?? null;
        $itemsCountMax = $filters['items_count_max'] ?? null;

        // Remove custom filters from array before passing to parent
        unset(
            $filters['type'],
            $filters['sort_by'],
            $filters['is_schedule'],
            $filters['category_id'],
            $filters['price_min'],
            $filters['price_max'],
            $filters['rating_min'],
            $filters['items_count_min'],
            $filters['items_count_max']
        );

        // Apply base query builder first (search, sort, favorites)
        $query = parent::queryBuilder($query, $filters, $config);

        // Calculate minimum price within basket
        $query->withMin('items', 'price');

        // Apply latest ordering by default
        if (empty($config['sortField'])) {
            $query->latest();
        }

        // Filter by schedule status
        if ($isSchedule !== null) {
            $query->where('is_schedule', $isSchedule);
        }

        // Category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Price range filter
        if ($priceMin || $priceMax) {
            $query = $this->applyPriceFilter($query, [
                'price_min' => $priceMin,
                'price_max' => $priceMax
            ], 'items_min_price');
        }

        // Rating filter
        if ($ratingMin) {
            $query->where('rating', '>=', $ratingMin);
        }

        // Items count filter
        if ($itemsCountMin || $itemsCountMax) {
            $query->whereHas('items', function ($q) {}, '>=', $itemsCountMin ?? 0);

            if ($itemsCountMax) {
                $query->whereHas('items', function ($q) {}, '<=', $itemsCountMax);
            }
        }

        // Type filters
        if ($type) {
            $this->applyTypeFilters($query, $type);
        }

        // Sort by
        if ($sort) {
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
            'price_desc' => $query->orderBy('items_min_price', 'desc'),
            'price_asc'  => $query->orderBy('items_min_price', 'asc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'rating_desc' => $query->orderBy('rating', 'desc'),
            'rating_asc'  => $query->orderBy('rating', 'asc'),
            default      => null,
        };
    }
}
