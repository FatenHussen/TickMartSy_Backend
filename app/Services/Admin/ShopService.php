<?php

namespace App\Services\Admin;

use App\Models\Shop;
use App\Models\Store;
use App\Services\Base\MediaService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Shop\AllResource;
use App\Http\Resources\Shop\AdminOneResource;

class ShopService extends BaseService
{

    public function __construct(Shop $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['name', 'description'];
        $this->sortableFields   = ['id'];
        $this->relations = ['vendor', 'services', 'area'];
        $this->pagination = true;

        $this->syncRelations = [
            'services'   => 'service_ids',
            'badges'   => 'badges',
        ];

        $this->mediaCollections = [
            // 'logo' => [
            //     'collection' => 'logo',
            //     'type'       => 'single',
            // ],
            'cover_images' => [
                'collection' => 'cover',
                'type'       => 'multiple',
            ],
        ];

        $this->singleImages = [
            'logo'  => 'logo',
        ];
    }
}
