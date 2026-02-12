<?php

namespace App\Http\Controllers\Admin\UserBasketSchedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserBasketSchedule\FilterRequest;
use App\Services\Admin\UserBasketScheduleService;
use Illuminate\Http\Request;

class UserBasketScheduleController extends Controller
{
    protected $service;

    public function __construct(UserBasketScheduleService $service)
    {
        $this->service = $service;
    }

    /**
     * List all user basket schedules
     */
    public function index(Request $request)
    {
        $filters = app(FilterRequest::class)->validated();

        $config = [
            'search'     => $request->input('search'),
            'sortField'  => $request->input('sort_field') ?? 'id',
            'sortOrder'  => $request->input('sort_order') ?? 'desc',
            'page'       => (int) $request->input('page', 1),
            'per_page'   => (int) $request->input('per_page', 10),
        ];

        $res = $this->service->getAll($filters, $config);
        return $this->sendResponse(data: $res);
    }

    /**
     * Show single user basket schedule
     */
    public function show($id)
    {
        $res = $this->service->getOne($id);
        return $this->sendResponse(data: $res);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $stats = $this->service->getStatistics();
        return $this->sendResponse(data: $stats);
    }

    /**
     * Get user basket schedules by user
     */
    public function byUser($userId)
    {
        $res = $this->service->getByUser($userId);
        return $this->sendResponse(data: $res);
    }

    /**
     * Get user basket schedules by schedule
     */
    public function bySchedule($scheduleId)
    {
        $res = $this->service->getBySchedule($scheduleId);
        return $this->sendResponse(data: $res);
    }
}
