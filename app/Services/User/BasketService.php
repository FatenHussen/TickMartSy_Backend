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

        return $query;
    }
}
