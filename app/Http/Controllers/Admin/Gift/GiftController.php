<?php

namespace App\Http\Controllers\Admin\Gift;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Gift\BulkStoreRequest;
use App\Http\Requests\Admin\Gift\FilterRequest;
use App\Http\Requests\Admin\Gift\StoreRequest;
use App\Http\Requests\Admin\Gift\UpdateRequest;
use App\Services\Admin\GiftService;
use Illuminate\Http\JsonResponse;

class GiftController extends BaseCRUDController
{
    public function __construct(GiftService $service)
    {
        $this->service= $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    public function bulkStore(BulkStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $gifts = $this->service->bulkCreate($data);
        return $this->sendResponse($gifts, 'تم إنشاء الهدايا بنجاح');
    }
}
