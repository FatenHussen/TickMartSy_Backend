<?php

namespace App\Http\Controllers\Admin\UserPoint;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserPoint\UserPointWalletResource;
use App\Http\Resources\Admin\UserPoint\UserPointTransactionResource;
use App\Http\Resources\Admin\UserPoint\UserPointSummaryResource;
use App\Models\User;
use App\Models\PointWallet;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class UserPointController extends Controller
{
    /**
     * Get all users with their point wallets
     */
    public function index(Request $request)
    {
        $query = User::with(['pointWallet', 'pointWallet.transactions'])
            ->whereHas('pointWallet');

        // Filter by balance range
        if ($request->has('balance_min')) {
            $query->whereHas('pointWallet', function ($q) use ($request) {
                $q->where('balance', '>=', $request->balance_min);
            });
        }

        if ($request->has('balance_max')) {
            $query->whereHas('pointWallet', function ($q) use ($request) {
                $q->where('balance', '<=', $request->balance_max);
            });
        }

        // Search by user name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        // Sort
        $sortField = $request->get('sortField', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');

        if ($sortField === 'balance') {
            $query->join('point_wallets', 'users.id', '=', 'point_wallets.user_id')
                  ->orderBy('point_wallets.balance', $sortOrder)
                  ->select('users.*');
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => UserPointSummaryResource::collection($users->items()),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ],
        ]);
    }

    /**
     * Get specific user's point wallet details
     */
    public function show($userId)
    {
        $user = User::with(['pointWallet', 'pointWallet.transactions.rule'])
            ->findOrFail($userId);

        if (!$user->pointWallet) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a point wallet',
            ], 404);
        }

        return $this->sendResponse(data: new UserPointWalletResource($user));
    }

    /**
     * Get user's point transactions
     */
    public function transactions($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        $query = PointTransaction::with(['rule', 'admin'])
            ->where('user_id', $userId);

        // Filter by source
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->get('sortField', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'items' => UserPointTransactionResource::collection($transactions->items()),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    /**
     * Add points to user (manual adjustment by admin)
     */
    public function addPoints(Request $request, $userId)
    {
        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($userId);

        // Get or create wallet
        $wallet = $user->pointWallet()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'balance' => 0,
        ]);

        // Create transaction
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'created_by_admin_id' => auth('admin')->id(),
            'source' => 'admin_adjustment',
            'points' => $request->points,
            'status' => 'earned',
            'reason' => $request->reason,
        ]);

        // Update wallet balance
        $wallet->increment('balance', $request->points);
        $wallet->update(['last_earned_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Points added successfully',
            'data' => [
                'transaction' => new UserPointTransactionResource($transaction),
                'new_balance' => $wallet->fresh()->balance,
            ],
        ]);
    }

    /**
     * Deduct points from user (manual adjustment by admin)
     */
    public function deductPoints(Request $request, $userId)
    {
        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($userId);
        $wallet = $user->pointWallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a point wallet',
            ], 404);
        }

        if ($wallet->balance < $request->points) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points balance',
            ], 400);
        }

        // Create transaction
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'created_by_admin_id' => auth('admin')->id(),
            'source' => 'admin_adjustment',
            'points' => -$request->points,
            'status' => 'redeemed',
            'reason' => $request->reason,
        ]);

        // Update wallet balance
        $wallet->decrement('balance', $request->points);

        return response()->json([
            'success' => true,
            'message' => 'Points deducted successfully',
            'data' => [
                'transaction' => new UserPointTransactionResource($transaction),
                'new_balance' => $wallet->fresh()->balance,
            ],
        ]);
    }

    /**
     * Get point statistics
     */
    public function statistics()
    {
        $totalUsers = User::whereHas('pointWallet')->count();
        $totalPoints = PointWallet::sum('balance');
        $totalEarned = PointTransaction::where('status', 'earned')->sum('points');
        $totalRedeemed = PointTransaction::where('status', 'redeemed')->sum('points');
        $totalTransactions = PointTransaction::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users_with_points' => $totalUsers,
                'total_points_in_system' => $totalPoints,
                'total_points_earned' => $totalEarned,
                'total_points_redeemed' => abs($totalRedeemed),
                'total_transactions' => $totalTransactions,
                'average_balance_per_user' => $totalUsers > 0 ? round($totalPoints / $totalUsers, 2) : 0,
            ],
        ]);
    }
}
