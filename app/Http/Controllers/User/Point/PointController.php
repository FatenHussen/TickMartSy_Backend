<?php

namespace App\Http\Controllers\User\Point;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\User\Point\TransactionFilterRequest;
use App\Http\Requests\User\Point\RedeemPointsRequest;
use App\Services\PointService;

class PointController extends BaseCRUDController
{
    public function __construct(PointService $service)
    {
        $this->service = $service;
        $this->filterRequest = TransactionFilterRequest::class;
    }

    /**
     * Get user points summary
     */
    public function summary()
    {
        $userId = auth('user')->id();
        $summary = $this->service->getUserPointsSummary($userId);

        return $this->sendResponse(data: $summary);
    }

    /**
     * Get user transactions history (uses BaseCRUDController index with filters)
     */
    public function transactions()
    {
        return $this->index(request());
    }

    /**
     * Redeem points for rewards
     */
    public function redeem(RedeemPointsRequest $request)
    {
        $userId = auth('user')->id();
        
        $transaction = $this->service->redeemPoints(
            $userId,
            $request->points,
            $request->reason,
            $request->reference_type,
            $request->reference_id
        );

        if (!$transaction) {
            return $this->sendError(
                message: 'Insufficient points balance',
                code: 400
            );
        }

        return $this->sendResponse(
            message: 'Points redeemed successfully',
            data: $transaction
        );
    }

    /**
     * Get transactions statistics by status
     */
    public function statistics()
    {
        $userId = auth('user')->id();
        $counts = $this->service->getTransactionsCountByStatus($userId);

        return $this->sendResponse(
            data: [
                'transactions_count' => $counts,
                'status_types' => [
                    'pending' => 'Pending transactions (not yet confirmed)',
                    'earned' => 'Earned points (added to balance)',
                    'expired' => 'Expired points (removed from balance)',
                    'redeemed' => 'Redeemed points (spent on rewards)',
                ]
            ]
        );
    }
}