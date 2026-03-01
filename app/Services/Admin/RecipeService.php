<?php

namespace App\Services\Admin;

use App\Http\Resources\Recipe\AdminOneResource;
use App\Http\Resources\Recipe\AllResource;
use App\Http\Resources\Recipe\OneResource;
use App\Models\Coupon;
use App\Models\Recipe;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class RecipeService extends BaseService
{
    public function __construct(Recipe $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['items', 'steps'];
        $this->searchableFields = ['id', 'name'];
        $this->syncRelations = [
            'items'   => 'items',
            'steps' => 'steps',
            'badges'   => 'badges',

        ];

        $this->singleImages = [
            'image'  => 'image',
        ];
    }
}
