<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\PopupCampaign\PopupCampaignCollection;
use App\Http\Resources\Admin\PopupCampaign\PopupCampaignResource;
use App\Models\PopupCampaign;
use App\Services\BaseService;

class PopupCampaignService extends BaseService
{
    public function __construct(PopupCampaign $model)
    {
        $this->model = $model;
        $this->resource = PopupCampaignResource::class;
        $this->collection = PopupCampaignCollection::class;
        $this->sortableFields = ['priority', 'created_at', 'updated_at', 'status'];
        $this->searchableFields = ['title', 'headline', 'description'];
    }
}
