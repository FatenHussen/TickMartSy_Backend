<?php

namespace App\Services\Admin;

use App\Models\Store;
use App\Services\Base\MediaService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class StoreService extends BaseService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}
  
}