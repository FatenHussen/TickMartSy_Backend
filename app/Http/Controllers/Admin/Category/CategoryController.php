<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Category\SortRequest;
use App\Http\Requests\Admin\Category\FilterRequest;
use App\Http\Requests\Admin\Category\StoreRequest;
use App\Http\Requests\Admin\Category\UpdateRequest;
use App\Services\Admin\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseCRUDController
{
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    public function sort(SortRequest $request)
    {
        $updated = $this->service->reorder(
            $request->validated('ordered_ids'),
            $request->validated('parent_id')
        );

        return $this->sendResponse(
            data: ['updated_count' => $updated],
            message: 'Category order updated successfully'
        );
    }

    /**
     * معاينة ما سيتأثر قبل تنفيذ الحذف.
     */
    public function deleteImpact($id): JsonResponse
    {
        return $this->sendResponse(data: $this->service->deleteImpact($id));
    }

    /**
     * تبويب "العناصر المرتبطة".
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
            message: __('custom.category_delete_impact.deleted_success')
        );
    }
}
