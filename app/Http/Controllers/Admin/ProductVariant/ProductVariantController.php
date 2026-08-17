<?php

namespace App\Http\Controllers\Admin\ProductVariant;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ProductVariant\FilterRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateRequest;
use App\Models\Product;
use App\Services\Admin\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends BaseCRUDController
{
    public function __construct(ProductVariantService $service)
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

    public function byProduct(Request $request, int $productId): JsonResponse
    {
        if (!Product::query()->whereKey($productId)->exists()) {
            return $this->sendError(message: 'المنتج غير موجود', code: 404);
        }

        $config = [
            'search' => $request->input('search'),
            'sortField' => $request->input('sort_field', 'id'),
            'sortOrder' => $request->input('sort_order', 'desc'),
            'page' => (int) $request->input('page', 1),
            'per_page' => (int) $request->input('per_page', 10),
        ];

        $res = $this->service->getAll(['product_id' => $productId], $config);

        return $this->sendResponse(data: $res);
    }
}
