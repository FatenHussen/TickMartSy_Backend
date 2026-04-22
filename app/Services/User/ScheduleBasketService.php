<?php

namespace App\Services\User;

use App\Http\Resources\Basket\AllResource;
use App\Http\Resources\Basket\OneResource;
use App\Models\Basket;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;

class ScheduleBasketService extends BaseService
{
    protected $model      = Basket::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'categories',
        'items',
        'items.product',
        'items.product.brand',
        'items.product.media',
        'items.variant',
        'basketImages',
        // 'items.companies',
        // 'items.companies.brand',
        'schedules',
        'favorites'
    ];
    protected $searchableFields = ['name'];
    protected $sortableFields   = ['id'];
    protected $pagination = true;

    public function query(array $filters)
    {
        $scheduleDays = $filters['schedule_days'] ?? null;

        $query = Basket::query()->latest();


        // Filter by schedule status
        // if (isset($filters['is_schedule'])) {
        //     $query->where('is_schedule', $filters['is_schedule']);
        // }
        $query->where('is_schedule', 1);

        // Filter by schedule days
        if ($scheduleDays !== null) {
            $query->whereHas('schedules', function ($q) use ($scheduleDays) {
                $q->where('number_of_days', $scheduleDays)
                    ->where('is_active', true);
            });
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->where(function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($categoryQuery) use ($categoryId) {
                    $categoryQuery->where('categories.id', $categoryId);
                })->orWhere('category_id', $categoryId);
            });
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
        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters['type']);
        }

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


        return $query->where('is_active', true);
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
