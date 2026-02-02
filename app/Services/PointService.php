<?php

namespace App\Services;

use App\Models\PointWallet;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\PointEvent;
use App\Models\User;
use App\Services\BaseService;
use App\Http\Resources\Point\PointSummaryResource;
use App\Http\Resources\Point\PointTransactionResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PointService extends BaseService
{
    protected $model = PointTransaction::class;
    protected $resource = PointTransactionResource::class;
    protected $collection = PointTransactionResource::class;
    protected $relations = ['rule', 'admin', 'wallet'];

    /**
     * Enable/disable pagination for transactions
     */
    protected $pagination = true;

    /**
     * Override queryBuilder to add user filtering
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Always filter by current user for security
        $userId = auth('user')->id();
        $query->where('user_id', $userId);

        // Apply status filter if provided
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply search if provided
        if (!empty($config['search'])) {
            $search = $config['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhereHas('rule', function ($ruleQuery) use ($search) {
                      $ruleQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sortField = $config['sortField'] ?? 'created_at';
        $sortOrder = $config['sortOrder'] ?? 'desc';
        
        if (in_array($sortField, ['id', 'points', 'created_at', 'status'])) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
    public function getOrCreateWallet(int $userId): PointWallet
    {
        return PointWallet::firstOrCreate(
            ['user_id' => $userId],
            [
                'balance' => 0,
                'expire_at' => null,
                'last_earned_at' => null,
            ]
        );
    }

    /**
     * Award points to user based on rule
     */
    public function awardPoints(
        int $userId,
        string $ruleCode,
        ?float $orderAmount = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        string $status = 'earned'
    ): ?PointTransaction {
        $rule = PointRule::where('code', $ruleCode)
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            return null;
        }

        // Check minimum order amount
        if ($rule->min_order_amount && (!$orderAmount || $orderAmount < $rule->min_order_amount)) {
            return null;
        }

        // Calculate points
        $points = $this->calculatePoints($rule, $orderAmount);

        if ($points <= 0) {
            return null;
        }

        return $this->addPointsToWallet(
            $userId,
            $points,
            $rule->id,
            $ruleCode,
            $status,
            $referenceType,
            $referenceId,
            $rule->expires_after_days
        );
    }

    /**
     * Add points to user wallet
     */
    public function addPointsToWallet(
        int $userId,
        int $points,
        ?int $ruleId = null,
        string $source = 'manual',
        string $status = 'earned',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $expiresAfterDays = null,
        ?int $adminId = null,
        ?string $reason = null
    ): PointTransaction {
        return DB::transaction(function () use (
            $userId, $points, $ruleId, $source, $status, $referenceType, 
            $referenceId, $expiresAfterDays, $adminId, $reason
        ) {
            $wallet = $this->getOrCreateWallet($userId);

            // Create transaction
            $transaction = PointTransaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'rule_id' => $ruleId,
                'created_by_admin_id' => $adminId,
                'source' => $source,
                'points' => $points,
                'status' => $status,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'expires_at' => $expiresAfterDays ? now()->addDays($expiresAfterDays) : null,
                'reason' => $reason,
            ]);

            // Update wallet if points are earned
            if ($status === 'earned') {
                $this->updateWalletBalance($wallet, $points);
            }

            return $transaction;
        });
    }

    /**
     * Deduct points from user wallet
     */
    public function deductPoints(
        int $userId,
        int $points,
        string $reason,
        ?int $adminId = null
    ): ?PointTransaction {
        return DB::transaction(function () use ($userId, $points, $reason, $adminId) {
            $wallet = $this->getOrCreateWallet($userId);

            if ($wallet->balance < $points) {
                return null; // Insufficient balance
            }

            $transaction = PointTransaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'created_by_admin_id' => $adminId,
                'source' => 'manual_deduction',
                'points' => -$points,
                'status' => 'earned',
                'reason' => $reason,
            ]);

            $this->updateWalletBalance($wallet, -$points);

            return $transaction;
        });
    }

    /**
     * Update pending points to earned
     */
    public function confirmPendingPoints(string $referenceType, int $referenceId): int
    {
        $transactions = PointTransaction::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', 'pending')
            ->get();

        $confirmedCount = 0;

        foreach ($transactions as $transaction) {
            DB::transaction(function () use ($transaction, &$confirmedCount) {
                $transaction->update(['status' => 'earned']);
                
                $wallet = $transaction->wallet;
                $this->updateWalletBalance($wallet, $transaction->points);
                
                $confirmedCount++;
            });
        }

        return $confirmedCount;
    }

    /**
     * Mark event as completed to prevent duplicate points
     */
    public function markEventCompleted(int $userId, string $eventKey): bool
    {
        try {
            PointEvent::create([
                'user_id' => $userId,
                'event_key' => $eventKey,
            ]);
            return true;
        } catch (\Exception $e) {
            return false; // Event already exists
        }
    }

    /**
     * Check if event is already completed
     */
    public function isEventCompleted(int $userId, string $eventKey): bool
    {
        return PointEvent::where('user_id', $userId)
            ->where('event_key', $eventKey)
            ->exists();
    }

    /**
     * Get user points summary
     */
    public function getUserPointsSummary(int $userId): PointSummaryResource
    {
        $wallet = $this->getOrCreateWallet($userId);
        
        $pendingPoints = PointTransaction::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('points');

        $expiredPoints = PointTransaction::where('user_id', $userId)
            ->where('status', 'expired')
            ->sum('points');

        $redeemedPoints = PointTransaction::where('user_id', $userId)
            ->where('status', 'redeemed')
            ->sum('points');

        $summaryData = [
            'balance' => $wallet->balance,
            'pending_points' => $pendingPoints,
            'expired_points' => abs($expiredPoints),
            'redeemed_points' => abs($redeemedPoints),
            'expire_at' => $wallet->expire_at,
            'last_earned_at' => $wallet->last_earned_at,
        ];

        return new \App\Http\Resources\Point\PointSummaryResource($summaryData);
    }

    /**
     * Get user transactions history
     */
    public function getUserTransactions(int $userId, int $perPage = 15, ?string $status = null, array $config = [])
    {
        $query = PointTransaction::where('user_id', $userId)
            ->with(['rule', 'admin']);

        if ($status && in_array($status, ['pending', 'earned', 'expired', 'redeemed'])) {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', 'desc');

        $usePagination = $config['pagination'] ?? $this->pagination;

        if ($usePagination) {
            $result = $query->paginate($perPage);

            return [
                'transactions' => \App\Http\Resources\Point\PointTransactionResource::collection($result->items()),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
                'filter' => [
                    'status' => $status ?? 'all',
                ]
            ];
        } else {
            $result = $query->get();

            return [
                'transactions' => \App\Http\Resources\Point\PointTransactionResource::collection($result),
                'pagination' => null,
                'filter' => [
                    'status' => $status ?? 'all',
                ]
            ];
        }
    }

    /**
     * Expire old points (to be called by scheduled job)
     */
    public function expireOldPoints(): int
    {
        $expiredCount = 0;

        $walletsToExpire = PointWallet::where('expire_at', '<', now())
            ->where('balance', '>', 0)
            ->get();

        foreach ($walletsToExpire as $wallet) {
            DB::transaction(function () use ($wallet, &$expiredCount) {
                // Create expiration transaction
                PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'source' => 'expiration',
                    'points' => -$wallet->balance,
                    'status' => 'expired',
                    'reason' => 'Points expired due to inactivity',
                ]);

                // Reset wallet
                $wallet->update([
                    'balance' => 0,
                    'expire_at' => null,
                ]);

                $expiredCount++;
            });
        }

        return $expiredCount;
    }

    /**
     * Calculate points based on rule
     */
    private function calculatePoints(PointRule $rule, ?float $orderAmount = null): int
    {
        if ($rule->type === 'fixed') {
            return $rule->value;
        }

        if ($rule->type === 'percentage' && $orderAmount) {
            return (int) floor(($orderAmount * $rule->value) / 100);
        }

        return 0;
    }

    /**
     * Update wallet balance and expiry
     */
    private function updateWalletBalance(PointWallet $wallet, int $points): void
    {
        $newBalance = $wallet->balance + $points;
        
        $updateData = ['balance' => $newBalance];

        // If earning points, update expiry and last earned date
        if ($points > 0) {
            $updateData['last_earned_at'] = now();
            $updateData['expire_at'] = now()->addYear(); // Extend expiry by 1 year
        }

        $wallet->update($updateData);
    }

    /**
     * Redeem points for rewards/discounts
     */
    public function redeemPoints(
        int $userId,
        int $points,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $adminId = null
    ): ?\App\Http\Resources\Point\PointTransactionResource {
        $transaction = DB::transaction(function () use ($userId, $points, $reason, $referenceType, $referenceId, $adminId) {
            $wallet = $this->getOrCreateWallet($userId);

            // Check if user has enough points
            if ($wallet->balance < $points) {
                return null; // Insufficient balance
            }

            // Create redemption transaction
            $transaction = PointTransaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'created_by_admin_id' => $adminId,
                'source' => 'redemption',
                'points' => -$points, // Negative because points are being spent
                'status' => 'redeemed',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason,
            ]);

            // Update wallet balance
            $this->updateWalletBalance($wallet, -$points);

            return $transaction;
        });

        return $transaction ? new \App\Http\Resources\Point\PointTransactionResource($transaction) : null;
    }

    /**
     * Get user redeemed points total
     */
    public function getUserRedeemedPoints(int $userId): int
    {
        return abs(PointTransaction::where('user_id', $userId)
            ->where('status', 'redeemed')
            ->sum('points'));
    }

    /**
     * Get user transactions by type
     */
    public function getUserTransactionsByType(int $userId, string $type, int $perPage = 15, array $config = [])
    {
        $validTypes = ['pending', 'earned', 'expired', 'redeemed', 'all'];
        
        if (!in_array($type, $validTypes)) {
            $type = 'all';
        }

        $query = PointTransaction::where('user_id', $userId)
            ->with(['rule', 'admin']);

        if ($type !== 'all') {
            $query->where('status', $type);
        }

        $query->orderBy('created_at', 'desc');

        // Check if pagination is enabled (can be overridden by config)
        $usePagination = $config['pagination'] ?? $this->pagination;

        if ($usePagination) {
            $result = $query->paginate($perPage);

            return [
                'transactions' => \App\Http\Resources\Point\PointTransactionResource::collection($result->items()),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
                'filter' => [
                    'status' => $type,
                ]
            ];
        } else {
            $result = $query->get();

            return [
                'transactions' => \App\Http\Resources\Point\PointTransactionResource::collection($result),
                'pagination' => null,
                'filter' => [
                    'status' => $type,
                ]
            ];
        }
    }

    /**
     * Get transactions count by status
     */
    public function getTransactionsCountByStatus(int $userId): array
    {
        $counts = PointTransaction::where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'pending' => $counts['pending'] ?? 0,
            'earned' => $counts['earned'] ?? 0,
            'expired' => $counts['expired'] ?? 0,
            'redeemed' => $counts['redeemed'] ?? 0,
            'total' => array_sum($counts),
        ];
    }

    /**
     * Enable pagination for transactions
     */
    public function enablePagination(): self
    {
        $this->pagination = true;
        return $this;
    }

    /**
     * Disable pagination for transactions
     */
    public function disablePagination(): self
    {
        $this->pagination = false;
        return $this;
    }

    /**
     * Check if pagination is enabled
     */
    public function isPaginationEnabled(): bool
    {
        return $this->pagination;
    }
}