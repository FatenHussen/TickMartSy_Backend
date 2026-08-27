<?php

namespace App\Services\User;

use App\Models\NavMenuItem;

class NavMenuService
{
    public function __construct(private NavMenuItem $model) {}

    /**
     * Active menu items in display order, with their destination relations.
     * Category links are dropped when the category is missing, inactive, or soft-deleted.
     */
    public function getMenuForUser()
    {
        return $this->model
            ->active()
            ->with(['page', 'category', 'brand'])
            ->orderBy('order')
            ->get()
            ->filter(fn (NavMenuItem $item) => $this->isDestinationAvailable($item))
            ->values();
    }

    private function isDestinationAvailable(NavMenuItem $item): bool
    {
        return match ($item->type) {
            'category' => $item->category_id
                && $item->category
                && (bool) $item->category->is_active,
            'page' => $item->page_id && $item->page,
            'brand' => $item->brand_id && $item->brand,
            'url' => filled($item->url),
            'route' => filled($item->route_key),
            default => false,
        };
    }
}
