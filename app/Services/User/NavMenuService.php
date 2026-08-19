<?php

namespace App\Services\User;

use App\Models\NavMenuItem;

class NavMenuService
{
    public function __construct(private NavMenuItem $model) {}

    /**
     * Active menu items in display order, with their destination relations.
     */
    public function getMenuForUser()
    {
        return $this->model
            ->active()
            ->with(['page', 'category', 'brand'])
            ->orderBy('order')
            ->get();
    }
}
