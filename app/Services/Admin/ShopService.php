<?php

namespace App\Services\Admin;

use App\Models\Shop;
use App\Models\Store;
use App\Services\Base\MediaService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Shop\AllResource;
use App\Http\Resources\Shop\OneResource;
class ShopService extends BaseService
{
    public function __construct(Shop $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['vendor'];
        $this->pagination=true;
  
    }
}