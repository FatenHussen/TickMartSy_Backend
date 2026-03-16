<?php

namespace App\Http\Controllers\User;

use App\Exceptions\CustomExceptionWithMessage;
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

    /*
    |--------------------------------------------------------------------------
    | Affiliate Check
    |--------------------------------------------------------------------------
    */
    protected function getAffiliate()
    {
        $affiliate = auth('user')->user();

        if (
            !$affiliate ||
            !$affiliate->is_affiliate ||
            !$affiliate->affiliate_approved ||
            empty($affiliate->affiliate_id)
        ) {
            throw new CustomExceptionWithMessage('custom.affiliate.not_authorized', 403);
        }

        return $affiliate;
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    public function statistics()
    {
        $affiliate = $this->getAffiliate();

        $data = $this->marketService
            ->getStatistics($affiliate->affiliate_id);

        return $this->sendResponse(
            data: $data,
            message: __('custom.affiliate.stats_retrieved')
        );
    }



    public function profile()
    {
        $affiliate = $this->getAffiliate();

        $data = $this->marketService
            ->getProfile($affiliate->affiliate_id);

        return $this->sendResponse(
            data: $data,
            message: __('custom.affiliate.profile_retrieved')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
    public function orders(Request $request)
    {
        $affiliate = $this->getAffiliate();

        $perPage = $request->get('per_page', 10);

        $filters = [
            'from'        => $request->get('from'),
            'to'          => $request->get('to'),
            'coupon_code' => $request->get('coupon_code'),
        ];

        $data = $this->marketService
            ->getOrders($affiliate->affiliate_id, $filters, $perPage);

        $orders = $data['orders'];

        return $this->sendResponse(
            data: [
                'summary' => $data['summary'],
                'items' => AllResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                ],
            ],
            message: __('custom.affiliate.orders_retrieved')
        );
    }

    public function transactions(Request $request)
    {
        $affiliate = $this->getAffiliate();

        $perPage = $request->get('per_page', 10);

        $filters = [
            'type'       => $request->get('type'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
            'min_amount' => $request->get('min_amount'),
            'max_amount' => $request->get('max_amount'),
        ];

        $data = $this->marketService
            ->getTransactions($affiliate->affiliate_id, $filters, $perPage);

        $transactions = $data['transactions'];

        return $this->sendResponse(
            data: [
                'summary' => $data['summary'],
                'items' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page'    => $transactions->lastPage(),
                    'per_page'     => $transactions->perPage(),
                    'total'        => $transactions->total(),
                ],
            ],
            message: __('custom.affiliate.transactions_retrieved')
        );
    }

    public function requestWithdraw(Request $request)
    {
        $affiliate = $this->getAffiliate();

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $withdraw = $this->marketService
            ->createWithdraw($affiliate->affiliate_id, $request->amount);

        if (!$withdraw) {
            return $this->sendError(
                message: __('custom.affiliate.withdraw_request_failed')
            );
        }

        return $this->sendResponse(
            data: ['withdraw_request' => $withdraw],
            message: __('custom.affiliate.withdraw_request_submitted')
        );
    }

    public function withdrawRequests(Request $request)
    {
        $affiliate = $this->getAffiliate();

        $perPage = $request->get('per_page', 10);

        $filters = [
            'status'     => $request->get('status'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
            'min_amount' => $request->get('min_amount'),
            'max_amount' => $request->get('max_amount'),
        ];

        $data = $this->marketService
            ->getWithdrawRequests($affiliate->affiliate_id, $filters, $perPage);

        $requests = $data['withdraw_requests'];

        return $this->sendResponse(
            data: [
                'summary' => $data['summary'],
                'items' => $requests->items(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page'    => $requests->lastPage(),
                    'per_page'     => $requests->perPage(),
                    'total'        => $requests->total(),
                ],
            ],
            message: __('custom.affiliate.withdraw_requests_retrieved')
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Monthly Orders
    |--------------------------------------------------------------------------
    */
    public function monthlyOrders(Request $request)
    {
        $affiliate = $this->getAffiliate();

        $year = $request->get('year');

        $data = $this->marketService
            ->getMonthlyOrdersSummary($affiliate->affiliate_id, $year);

        return $this->sendResponse(
            data: $data,
            message: __('custom.affiliate.monthly_orders_retrieved')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Visit Counter
    |--------------------------------------------------------------------------
    */
    public function visit(Request $request)
    {
        $affiliateId = $request->affiliate_id;

        $user = User::where('affiliate_id', $affiliateId)->first();

        if ($user) {
            $user->increment('affiliate_visits');
        }

        return $this->sendResponse();
    }
}
