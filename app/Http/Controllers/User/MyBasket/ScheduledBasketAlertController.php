<?php

namespace App\Http\Controllers\User\MyBasket;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ScheduledBasketAlert\DecisionRequest;
use App\Http\Resources\ScheduledBasketAlertResource;
use App\Services\ScheduledBasketAlertService;

class ScheduledBasketAlertController extends Controller
{
    public function __construct(private readonly ScheduledBasketAlertService $service)
    {
    }

    public function decide(DecisionRequest $request, int $alertId)
    {
        $alert = $this->service->decide(
            userId: auth('user')->id(),
            alertId: $alertId,
            decision: $request->validated('decision'),
        );

        return $this->sendResponse(
            data: ScheduledBasketAlertResource::make($alert),
            message: 'تم حفظ قرارك بنجاح',
        );
    }
}
