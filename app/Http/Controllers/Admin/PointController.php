<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Point\AddPointsRequest;
use App\Http\Requests\Admin\Point\DeductPointsRequest;
use App\Services\PointService;
use App\Models\User;
use Illuminate\Http\Request;

class PointController extends BaseCRUDController
{
    protected PointService $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Add points to user manually
     */
    public function addPoints(AddPointsRequest $request)
    {
        $data = $request->validated();
        $adminId = auth('admin')->id();

        $transaction = $this->pointService->addPointsToWallet(
            userId: $data['user_id'],
            points: $data['points'],
            source: 'manual_addition',
            adminId: $adminId,
            reason: $data['reason']
        );

        return $this->sendResponse(
            data: $transaction,
            message: 'Points added successfully'
        );
    }

    /**
     * Deduct points from user manually
     */
    public function deductPoints(DeductPointsRequest $request)
    {
        $data = $request->validated();
        $adminId = auth('admin')->id();

        $transaction = $this->pointService->deductPoints(
            userId: $data['user_id'],
            points: $data['points'],
            reason: $data['reason'],
            adminId: $adminId
        );

        if (!$transaction) {
            return $this->sendResponse(
                success: false,
                message: 'Insufficient points balance'
            );
        }

        return $this->sendResponse(
            data: $transaction,
            message: 'Points deducted successfully'
        );
    }

    /**
     * Get user points summary for admin
     */
    public function getUserSummary(Request $request)
    {
        $userId = $request->get('user_id');
        
        if (!$userId) {
            return $this->sendResponse(
                success: false,
                message: 'User ID is required'
            );
        }

        $summary = $this->pointService->getUserPointsSummary($userId);
        $user = User::find($userId);

        return $this->sendResponse(
            data: [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ],
                'points_summary' => $summary
            ]
        );
    }

    /**
     * Get user transactions for admin
     */
    public function getUserTransactions(Request $request)
    {
        $userId = $request->get('user_id');
        
        if (!$userId) {
            return $this->sendResponse(
                success: false,
                message: 'User ID is required'
            );
        }

        $transactions = $this->pointService->getUserTransactions($userId, 20);

        return $this->sendResponse(
            data: [
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        );
    }
}