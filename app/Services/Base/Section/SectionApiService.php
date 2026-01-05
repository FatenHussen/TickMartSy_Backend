<?php

namespace App\Services\Base\Section;

use App\Models\Section;
// use App\Services\Base\Section\TrendingProductsHandler;

class SectionApiService
{
    public function preview(Section $section, array $pageFilters = [])
    {
        return $this->execute($section, $pageFilters, limit: 8);
    }

    public function full(Section $section, array $pageFilters = [])
    {
        return $this->execute($section, $pageFilters, paginate: 20);
    }

    protected function execute(Section $section, array $pageFilters = [], int $limit = null, int $paginate = null)
    {
        if ($section->type !== 'api') return null;

        $finalFilters =  $pageFilters;

        $handlerClass = $this->resolveHandler($section->api_method);

        if (!$handlerClass) return null;

        $query = app($handlerClass)->query($finalFilters);

        return $limit ? $query->limit($limit)->get() : $query->paginate($paginate);
    }

    protected function resolveHandler(string $method)
    {
        $map = [
            'trending_products' => TrendingProductsHandler::class,
            // 'latest_products' => \App\Services\Section\Api\Handlers\LatestProductsHandler::class,
        ];

        return $map[$method] ?? null;
    }
}
