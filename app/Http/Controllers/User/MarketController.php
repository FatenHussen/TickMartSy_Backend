<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Resources\Order\AllResource;
use App\Services\User\MarketService;
use Illuminate\Http\Request;

class MarketController extends BaseCRUDController
{
    protected MarketService $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }

    public function statistics()
    {
        $data = $this->marketService->getStatistics();
        return $this->sendResponse(data: $data, message: 'Affiliate statistics retrieved successfully');
    }

    public function orders(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $filters = [
            'from'        => $request->get('from'),
            'to'          => $request->get('to'),
            'coupon_code' => $request->get('coupon_code'),
        ];

        $data = $this->marketService->getOrders($filters, $perPage);

        $orders = $data['orders'];

        return $this->sendResponse(data: [
            'summary' => $data['summary'],

            'items' => AllResource::collection($orders->items()),

            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], message: 'Affiliate orders retrieved successfully');
    }


    public function transactions(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $filters = [
            'type'       => $request->get('type'),
            'status'     => $request->get('status'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
            'min_amount' => $request->get('min_amount'),
            'max_amount' => $request->get('max_amount'),
        ];

        $data = $this->marketService->getTransactions($filters, $perPage);

        $transactions = $data['transactions'];

        return $this->sendResponse(data: [
            'summary' => $data['summary'],

            'items' => $transactions->items(),

            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ], message: 'Affiliate transactions retrieved successfully');
    }

    public function requestWithdraw(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $withdraw = $this->marketService->createWithdraw($request->amount);

        if (!$withdraw) {
            return $this->sendError(message: 'Withdraw request failed, Amount exceeds available balance');
        }

        return $this->sendResponse(
            data: ['withdraw_request' => $withdraw],
            message: 'Withdraw request submitted successfully'
        );
    }

    public function withdrawRequests(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $requests = $this->marketService->getWithdrawRequests($perPage);

        return $this->sendResponse(data: [
            'items' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ], message: 'Affiliate withdraw requests retrieved successfully');
    }
}
