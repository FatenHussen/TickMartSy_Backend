<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Product\FilterRequest;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Http\Resources\Admin\Product\OneResource;
use App\Services\Admin\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseCRUDController
{
    public function __construct(ProductService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    /**
     * قبول المنتج
     *
     * @param int $id
     * @return JsonResponse
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $product = $this->service->approve($id);

            return $this->sendResponse(
                new OneResource($product),
                'تم قبول المنتج بنجاح'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    /**
     * رفض المنتج
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'يجب إدخال سبب الرفض',
            'rejection_reason.string' => 'سبب الرفض يجب أن يكون نص',
            'rejection_reason.max' => 'سبب الرفض يجب ألا يتجاوز 1000 حرف',
        ]);

        try {
            $product = $this->service->reject($id, $request->rejection_reason);

            return $this->sendResponse(
                new OneResource($product),
                'تم رفض المنتج'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
