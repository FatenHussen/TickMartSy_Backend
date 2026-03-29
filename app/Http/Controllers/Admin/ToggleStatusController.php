<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ToggleStatusRequest;
use App\Services\Admin\ToggleStatusService;
use Illuminate\Http\JsonResponse;

class ToggleStatusController extends Controller
{
    protected $service;

    public function __construct(ToggleStatusService $service)
    {
        $this->service = $service;
    }

    /**
     * Toggle is_active status for any model
     *
     * @param ToggleStatusRequest $request
     * @return JsonResponse
     */
    public function toggleStatus(ToggleStatusRequest $request): JsonResponse
    {
        $result = $this->service->toggleStatus(
            $request->type,
            $request->id,
            $request->is_active
        );

        return response()->json($result);
    }
}
