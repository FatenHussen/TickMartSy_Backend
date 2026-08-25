<?php

namespace App\Http\Controllers\Admin\CustomOrderRequest;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\CustomOrderRequest\CancelRequest;
use App\Http\Requests\Admin\CustomOrderRequest\ConvertRequest;
use App\Http\Requests\Admin\CustomOrderRequest\FilterRequest;
use App\Services\Admin\CustomOrderRequestService;

class CustomOrderRequestController extends BaseIndexController
{
    public function __construct(CustomOrderRequestService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    public function convert(ConvertRequest $request, int $id)
    {
        $data = $request->validated();

        foreach ($request->file('items', []) as $index => $itemFiles) {
            if (isset($itemFiles['invoice_image'])) {
                $data['items'][$index]['invoice_image'] = $itemFiles['invoice_image'];
            }
        }

        $res = $this->service->convert($id, $data);

        return $this->sendResponse(
            data: $res,
            message: __('custom.custom_order_requests.converted_successfully')
        );
    }

    public function cancel(CancelRequest $request, int $id)
    {
        $res = $this->service->cancelByAdmin($id, $request->input('rejection_reason'));

        return $this->sendResponse(
            data: $res,
            message: __('custom.custom_order_requests.cancelled_by_admin_successfully')
        );
    }
}
