<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Http\Resources\Coupon\OneResource as CouponResource;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketService
{
    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    public function getStatistics(string $affiliateId): array
    {
        $ordersQuery = Order::where('affiliate_id', $affiliateId);

        $totalOrders = (clone $ordersQuery)->count();

        $deliveredQuery = (clone $ordersQuery)
            ->where('status', OrderStatus::DELIVERED->value);

        $deliveredOrders = (clone $deliveredQuery)->count();

        $totalSales = (clone $ordersQuery)->sum('total');

        $earnedCommission = (clone $deliveredQuery)
            ->sum(DB::raw('total * (affiliate_rate / 100)'));

        $pendingCommission = (clone $ordersQuery)
            ->where('status', '!=', OrderStatus::DELIVERED->value)
            ->sum(DB::raw('total * (affiliate_rate / 100)'));

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'withdraw')
            ->sum('amount');

        return [
            'stats' => [
                'total_orders'      => $totalOrders,
                'delivered_orders'  => $deliveredOrders,
                'total_sales'       => round($totalSales, 2),
                'earned_commission' => round($earnedCommission, 2),
                'pending_earnings'  => round($pendingCommission, 2),
                'withdrawn'         => round($totalWithdrawn, 2),
                'available_balance' => round($earnedCommission - $totalWithdrawn, 2),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    public function getProfile(string $affiliateId): array
    {
        $affiliate = User::where('affiliate_id', $affiliateId)
            ->where('is_affiliate', true)
            ->where('affiliate_approved', true)
            ->first();

        if (!$affiliate) {
            return [];
        }

        $activeCoupon = Coupon::where('affiliate_id', $affiliateId)
            ->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereColumn('used_count', '<', 'max_uses')
            ->first();

        return [
            'affiliate_id'   => $affiliate->affiliate_id,
            'affiliate_link' => url("affiliate/{$affiliate->affiliate_id}"),
            'rate'           => $affiliate->affiliate_rate,
            'total_visites'  => $affiliate->affiliate_visits,
            'coupon'    => $activeCoupon
                ? CouponResource::make($activeCoupon)
                : null,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
    public function getOrders(string $affiliateId, array $filters, int $perPage): array
    {
        $query = Order::where('affiliate_id', $affiliateId)
            ->with('coupon');

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (!empty($filters['coupon_code'])) {
            $query->whereHas('coupon', function ($q) use ($filters) {
                $q->where('code', $filters['coupon_code']);
            });
        }

        $query->orderBy('created_at', 'asc');

        $collection = (clone $query)->get();

        $delivered = $collection
            ->where('status', OrderStatus::DELIVERED->value);

        return [
            'orders' => $query->paginate($perPage),
            'summary' => [
                'total_orders'      => $collection->count(),
                'delivered_orders'  => $delivered->count(),
                'total_sales'       => round($collection->sum('total'), 2),
                'earned_commission' => round(
                    $delivered->sum(fn($o) => $o->total * ($o->affiliate_rate / 100)),
                    2
                ),
                'pending_earnings'  => round(
                    $collection
                        ->where('status', '!=', OrderStatus::DELIVERED->value)
                        ->sum(fn($o) => $o->total * ($o->affiliate_rate / 100)),
                    2
                ),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */
    public function getTransactions(string $affiliateId, array $filters, int $perPage): array
    {
        $query = AffiliateWalletTransaction::where('affiliate_id', $affiliateId);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        $query->orderBy('created_at', 'desc');

        $collection = (clone $query)->get();

        $totalCommissions = $collection
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = $collection
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingWithdrawals = $collection
            ->where('type', 'withdraw')
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'transactions' => $query->paginate($perPage),
            'summary' => [
                'transactions_count' => $collection->count(),
                'total_commissions'  => round($totalCommissions, 2),
                'total_withdrawn'    => round($totalWithdrawn, 2),
                'pending_withdrawals' => round($pendingWithdrawals, 2),
                'available_balance'  => round($totalCommissions - $totalWithdrawn, 2),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create Withdraw
    |--------------------------------------------------------------------------
    */
    public function createWithdraw(string $affiliateId, float $amount): ?AffiliateWithdrawRequest
    {
        $totalCommission = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'commission')
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'withdraw')
            ->sum('amount');

        $availableBalance = $totalCommission - $totalWithdrawn;

        if ($amount > $availableBalance) {
            return null;
        }

        return AffiliateWithdrawRequest::create([
            'affiliate_id' => $affiliateId,
            'amount'       => $amount,
            'status'       => 'pending',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Withdraw Requests
    |--------------------------------------------------------------------------
    */
    public function getWithdrawRequests(string $affiliateId, array $filters, int $perPage): array
    {
        $query = AffiliateWithdrawRequest::where('affiliate_id', $affiliateId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        $query->orderBy('created_at', 'desc');

        $collection = (clone $query)->get();

        return [
            'withdraw_requests' => $query->paginate($perPage),
            'summary' => [
                'total_requests' => $collection->count(),
                'total_withdrawn' => round(
                    $collection->where('status', 'completed')->sum('amount'),
                    2
                ),
                'pending_amount' => round(
                    $collection->where('status', 'pending')->sum('amount'),
                    2
                ),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Monthly Orders Summary
    |--------------------------------------------------------------------------
    */
    public function getMonthlyOrdersSummary(string $affiliateId, int $year = null): array
    {
        $year = $year ?? now()->year;

        $results = Order::selectRaw("
                MONTH(created_at) as month,
                COUNT(*) as completed_orders,
                SUM(total * (affiliate_rate / 100)) as earned_commission
            ")
            ->where('affiliate_id', $affiliateId)
            ->where('status', OrderStatus::DELIVERED->value)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->orderBy(DB::raw("MONTH(created_at)"))
            ->get();

        $monthlyPerformance = [];

        for ($m = 1; $m <= 12; $m++) {
            $data = $results->firstWhere('month', $m);

            $monthlyPerformance[date('F', mktime(0, 0, 0, $m, 1))] = [
                'completed_orders' => $data->completed_orders ?? 0,
                'earned_commission' => round($data->earned_commission ?? 0, 2),
            ];
        }

        return [
            'year' => $year,
            'monthly_performance' => $monthlyPerformance,
        ];
    }
}
