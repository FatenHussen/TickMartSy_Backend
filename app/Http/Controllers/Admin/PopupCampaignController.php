<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\PopupCampaignRequest;
use App\Services\Admin\PopupCampaignService;

class PopupCampaignController extends BaseCRUDController
{
    public function __construct(PopupCampaignService $service)
    {
        $this->service = $service;
        $this->createRequest = PopupCampaignRequest::class;
        $this->updateRequest = PopupCampaignRequest::class;
    }
}
