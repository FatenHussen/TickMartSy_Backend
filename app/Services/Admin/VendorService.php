<?php

namespace App\Services\Admin;

use App\Http\Resources\Vendor\AllResource;
use App\Http\Resources\Vendor\OneResource;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class VendorService extends BaseService
{
    public function __construct(Vendor $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'owner_name', 'owner_phone'];
    }
}
