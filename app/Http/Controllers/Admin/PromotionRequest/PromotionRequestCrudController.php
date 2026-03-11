<?php

namespace App\Http\Controllers\Admin\PromotionRequest;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\PromotionRequest\FilterRequest;
use App\Http\Requests\Admin\PromotionRequest\ApproveRequest;
use App\Http\Requests\Admin\PromotionRequest\RejectRequest;
use App\Services\Admin\PromotionRequestService;
use Illuminate\Http\JsonResponse;

class PromotionRequestCrudController extends BaseCRUDController
{
    public function __construct(PromotionRequestService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    /**
     * قبول طلب الترويج
     *
     * @param int $id
     * @param ApproveRequest $request
     * @return JsonResponse
     */
    public function approve(int $id, ApproveRequest $request): JsonResponse
    {
        try {
            $promotionRequest = $this->service->approve($id, $request->validated());

            return $this->sendResponse(
                new ($this->service->resource)($promotionRequest),
                'تم قبول طلب الترويج بنجاح'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    /**
     * رفض طلب الترويج
     *
     * @param int $id
     * @param RejectRequest $request
     * @return JsonResponse
     */
    public function reject(int $id, RejectRequest $request): JsonResponse
    {
        try {
            $promotionRequest = $this->service->reject($id, $request->validated());

            return $this->sendResponse(
                new ($this->service->resource)($promotionRequest),
                'تم رفض طلب الترويج'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    /**
     * إحصائيات طلبات الترويج
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->service->getStats();

            return $this->sendResponse($stats, 'تم جلب الإحصائيات بنجاح');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
