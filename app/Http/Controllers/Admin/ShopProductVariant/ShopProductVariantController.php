<?php

namespace App\Http\Controllers\Admin\ShopProductVariant;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ShopProductVariant\FilterRequest;
use App\Http\Requests\Admin\ShopProductVariant\UpdateRequest;
use App\Services\Admin\ShopProductVariantService;
use Illuminate\Http\JsonResponse;

class ShopProductVariantController extends BaseCRUDController
{
    public function __construct(ShopProductVariantService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    /**
     * معاينة ما سيُحذف أو يتأثر قبل تنفيذ الحذف.
     */
    public function deleteImpact($id): JsonResponse
    {
        return $this->sendResponse(data: $this->service->deleteImpact($id));
    }

    /**
     * الحذف: يعيد 409 مع تفاصيل التأثير إن لم يُرسل confirm=true.
     */
    public function destroy($id): JsonResponse
    {
        $impact = $this->service->deleteWithConfirmation($id, request()->boolean('confirm'));

        return $this->sendResponse(
            data: $impact,
            message: __('custom.products.delete_impact.deleted_success')
        );
    }
}
