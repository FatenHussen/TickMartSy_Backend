<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateStatusRequest;
use App\Services\Driver\DriverService;

class DriverController extends Controller
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    /**
     * Update driver status
     */
    public function updateStatus(UpdateStatusRequest $request)
    {
        $driverId = auth('driver')->id();
        $status = $request->validated()['status'];
        
        $driver = $this->driverService->updateStatus($driverId, $status);

        return $this->sendResponse(data: $driver);
    }
}