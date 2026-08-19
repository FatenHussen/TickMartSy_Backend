<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Category\CategoryAttribute\FilterRequest;
use App\Http\Requests\Admin\Category\CategoryAttribute\StoreRequest;
use App\Http\Requests\Admin\Category\CategoryAttribute\UpdateRequest;
use App\Services\Admin\CategoryAttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryAttributeController extends BaseCRUDController
{
    public function __construct(CategoryAttributeService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    /**
     * معاينة ما سيتأثر قبل تنفيذ الحذف.
     */
    public function deleteImpact($id): JsonResponse
    {
        return $this->sendResponse(data: $this->service->deleteImpact($id));
    }

    /**
     * تبويب "العناصر المرتبطة": المتغيّرات التي تستخدم قيم هذه الخاصية.
     */
    public function linkedItems(Request $request, $id): JsonResponse
    {
        $result = $this->service->linkedItems(
            $id,
            (int) $request->input('page', 1),
            $this->resolvePerPage($request)
        );

        return $this->sendResponse(data: $result);
    }

    /**
     * الحذف: يعيد 409 مع تفاصيل التأثير إن لم يُرسل confirm=true.
     */
    public function destroy($id): JsonResponse
    {
        $impact = $this->service->deleteWithConfirmation($id, request()->boolean('confirm'));

        return $this->sendResponse(
            data: $impact,
            message: __('custom.category_attribute_delete_impact.deleted_success')
        );
    }
}
