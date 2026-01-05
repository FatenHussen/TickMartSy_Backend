<?php

namespace App\Services\Base\Section;

use App\Models\Product;


class TrendingProductsHandler
{
    public function query(array $filters)
    {
        $query = Product::query()->latest();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        return $query;
    }
}
