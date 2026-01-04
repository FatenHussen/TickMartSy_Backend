<?php

namespace App\Services\Admin;

use App\Models\Shop;
use App\Models\Store;
use App\Services\Base\MediaService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Banner\AllResource;
use App\Http\Resources\Banner\OneResource;
use App\Models\Banner;

class BannerService extends BaseService
{
    public function __construct(Banner $model)
    {
        $this->model      = $model;
        $this->resource   = AllResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['title', 'description'];
        $this->sortableFields   = ['id', 'order'];
        $this->pagination = true;
        // $this->mediaCollections = [
        //     'image' => [
        //         'collection' => 'logo',
        //         'type'       => 'single',
        //     ],
        // ];

        $this->singleImages = [
            'image'
        ];
    }
}
