<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Resources\Order\AllResource;
use App\Models\User;
use App\Services\User\MarketService;
use Illuminate\Http\Request;

class MarketController extends BaseCRUDController
{
    protected MarketService $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }

    // ===============================
    // دالة تحقق المسوّق
    // ===============================
    protected function checkAffiliate(): void
    {
        $affiliate = auth('user')->user();

        if (
            !$affiliate ||
            !$affiliate->is_affiliate ||
            !$affiliate->affiliate_approved ||
            empty($affiliate->affiliate_id)
        ) {
            abort(403, 'You are not authorized as an affiliate.');
        }
    }

    public function statistics()
    {
        $this->checkAffiliate();

        $data = $this->marketService->getStatistics();
        return $this->sendResponse(data: $data, message: 'Affiliate statistics retrieved successfully');
    }

    public function orders(Request $request)
    {
        $this->checkAffiliate();

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
        $this->checkAffiliate();

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
        $this->checkAffiliate();

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
        $this->checkAffiliate();

        $perPage = $request->get('per_page', 10);

        $filters = [
            'status'     => $request->get('status'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
            'min_amount' => $request->get('min_amount'),
            'max_amount' => $request->get('max_amount'),
        ];

        // استدعاء الـ service
        $data = $this->marketService->getWithdrawRequests($filters, $perPage);

        $requests = $data['withdraw_requests']; // الـ paginator
        $summary  = $data['summary'];           // الإحصائيات

        return $this->sendResponse(data: [
            'summary' => $summary,
            'items' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ], message: 'Affiliate withdraw requests retrieved successfully');
    }

    public function visit(Request $request)
    {
        $affiliate_id = $request->affiliate_id;

        $user = User::where('affiliate_id', $affiliate_id)->first();

        if ($user) {
            $user->increment('affiliate_visits');
        }
        return $this->sendResponse();
    }

    public function monthlyOrders(Request $request)
    {
        $year = $request->get('year');
        $data = $this->marketService->getMonthlyOrdersSummary($year);

        return $this->sendResponse(
            data: $data,
            message: 'Monthly completed orders retrieved successfully'
        );
    }
}
