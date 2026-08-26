<?php

namespace App\Services\Admin;

use App\Models\SaleCountry;
use App\Services\BaseService;
use App\Http\Resources\Admin\SaleCountry\OneResource;
use App\Http\Resources\Admin\SaleCountry\AllResource;

class SaleCountryService extends BaseService
{
    protected $model      = SaleCountry::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    // Icons come from SaleCountrySeeder (emoji flags), not file upload

    protected $searchableFields = ['id', 'name'];
    protected $sortableFields   = ['id', 'name', 'created_at'];
}
