<?php

namespace App\Services\User;

use App\Http\Resources\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Category\AllResource;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['parent', 'children'];
        $this->pagination = true;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Apply name filter
        if (isset($filters['name'])) {
            $locale = app()->getLocale();
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ['%' . strtolower($filters['name']) . '%']);
        }

        // Apply parent_id filter
        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null || !$filters['parent_id']) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Shop filter - categories that have products in this shop
        if (!empty($filters['shop_id'])) {
            $query->whereHas('products', function ($q) use ($filters) {
                $q->whereHas('shopVariants', function ($sq) use ($filters) {
                    $sq->where('shop_id', $filters['shop_id']);
                });
            });
        }

        // Type filters
        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters['type']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    protected function applyTypeFilters($query, $type)
    {
        switch ($type) {
            case 'new':
                $query->orderBy('created_at', 'desc');
                break;

            case 'most_popular':
                // Most popular = categories with most products sold
                $query->withCount(['products as total_sales' => function ($q) {
                    $q->join('order_items', 'products.id', '=', 'order_items.product_id')
                      ->selectRaw('SUM(order_items.quantity)');
                }])->orderBy('total_sales', 'desc');
                break;

            case 'top_rated':
                // Top rated = categories with highest average product rating
                $query->withAvg('products as avg_rating', 'rating')
                      ->orderBy('avg_rating', 'desc');
                break;
        }
    }
}
