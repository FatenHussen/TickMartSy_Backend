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
        'items.product',
        'items.variant',
        'items.companies',
    ];
    protected $searchableFields = ['name'];
    protected $sortableFields   = ['id'];
    protected $pagination = true;
}
