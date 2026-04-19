<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Http\Resources\Coupon\OneResource as CouponResource;
use App\Models\AffiliateUserVisit;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketService
{
    public function registerVisitAndReward(?string $affiliateId): void
    {
        $visitorId = auth('user')->id();

        if (empty($affiliateId) || !$visitorId) {
            return;
        }

        DB::transaction(function () use ($affiliateId, $visitorId) {
            /** @var User|null $user */
            $user = User::where('affiliate_id', $affiliateId)
                ->lockForUpdate()
                ->first();

            if (!$user || $user->id === $visitorId) {
                return;
            }

            $visit = AffiliateUserVisit::firstOrCreate([
                'affiliate_id' => $affiliateId,
                'user_id' => $visitorId,
            ]);

            if (! $visit->wasRecentlyCreated) {
                return;
            }

            $user->increment('affiliate_visits');
            $user->refresh();

            if (
                !$user->is_affiliate ||
                !$user->affiliate_approved ||
                !$user->affiliate_visit_commission_enabled
            ) {
                return;
            }

            $threshold = (int) ($user->affiliate_visit_commission_threshold ?? 0);
            $amountPerStep = (float) ($user->affiliate_visit_commission_amount ?? 0);

            if ($threshold <= 0 || $amountPerStep <= 0) {
                return;
            }

            $eligibleSteps = intdiv((int) $user->affiliate_visits, $threshold);
            $rewardedSteps = (int) ($user->affiliate_visit_rewarded_steps ?? 0);

            if ($eligibleSteps <= $rewardedSteps) {
                return;
            }

            $newSteps = $eligibleSteps - $rewardedSteps;
            $rewardAmount = round($newSteps * $amountPerStep, 2);

            AffiliateWalletTransaction::create([
                'affiliate_id' => $user->affiliate_id,
                'type' => 'visit_commission',
                'amount' => $rewardAmount,
                'order_id' => null,
            ]);

            $user->update([
                'affiliate_visit_rewarded_steps' => $eligibleSteps,
            ]);
        });
    }

    private function commissionExpression(): string
    {
        return 'COALESCE(affiliate_commission_amount, total * (affiliate_rate / 100))';
    }

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
            ->sum(DB::raw($this->commissionExpression()));

        $pendingCommission = (clone $ordersQuery)
            ->where('status', '!=', OrderStatus::DELIVERED->value)
            ->sum(DB::raw($this->commissionExpression()));

        $visitCommission = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'visit_commission')
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'withdraw')
            ->sum('amount');

        return [
            'total_orders'      => $totalOrders,
            'delivered_orders'  => $deliveredOrders,
            'total_sales'       => round($totalSales, 2),
            'earned_commission' => round($earnedCommission + $visitCommission, 2),
            'pending_earnings'  => round($pendingCommission, 2),
            'withdrawn'         => round($totalWithdrawn, 2),
            'available_balance' => round(($earnedCommission + $visitCommission) - $totalWithdrawn, 2),
            'visit_commission' => round($visitCommission, 2),
            'top_products' => $this->getTopProducts($affiliateId, 10)
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
            ->valid()
            ->first();

        return [
            'affiliate_id'   => $affiliate->affiliate_id,
            'affiliate_link' => url("affiliate/{$affiliate->affiliate_id}"),
            'rate'           => $affiliate->affiliate_rate,
            'commission_type' => $affiliate->affiliate_commission_type,
            'fixed_commission' => $affiliate->affiliate_fixed_commission,
            'total_visites'  => $affiliate->affiliate_visits,
            'visit_commission_enabled' => (bool) $affiliate->affiliate_visit_commission_enabled,
            'visit_commission_threshold' => $affiliate->affiliate_visit_commission_threshold,
            'visit_commission_amount' => $affiliate->affiliate_visit_commission_amount,
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
                    $delivered->sum(function ($o) {
                        if ($o->affiliate_commission_amount !== null) {
                            return (float) $o->affiliate_commission_amount;
                        }

                        return $o->total * ($o->affiliate_rate / 100);
                    }),
                    2
                ),
                'pending_earnings'  => round(
                    $collection
                        ->where('status', '!=', OrderStatus::DELIVERED->value)
                        ->sum(function ($o) {
                            if ($o->affiliate_commission_amount !== null) {
                                return (float) $o->affiliate_commission_amount;
                            }

                            return $o->total * ($o->affiliate_rate / 100);
                        }),
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
            ->whereIn('type', ['commission', 'visit_commission'])
            // ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = $collection
            ->where('type', 'withdraw')
            // ->where('status', 'completed')
            ->sum('amount');


        return [
            'transactions' => $query->paginate($perPage),
            'summary' => [
                'transactions_count' => $collection->count(),
                'total_commissions'  => round($totalCommissions, 2),
                'total_withdrawn'    => round($totalWithdrawn, 2),
                'pending_withdrawals' => 0,
                'available_balance'  => 0,
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
            ->whereIn('type', ['commission', 'visit_commission'])
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
                    $collection->where('status', 'approved')->sum('amount'),
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
                SUM(" . $this->commissionExpression() . ") as earned_commission
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
    public function getTopProducts(string $affiliateId, int $limit = 10): array
    {
        $locale = app()->getLocale();

        $topProducts = OrderItem::query()
            ->selectRaw('products.id as product_id, products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.total) as total_sales')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('shop_product_variants', 'order_items.shop_product_variant_id', '=', 'shop_product_variants.id')
            ->join('product_variants', 'shop_product_variants.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.affiliate_id', $affiliateId)
            ->where('orders.status', \App\Enums\OrderStatus::DELIVERED->value)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        return $topProducts->map(fn($p) => [
            'product_id' => $p->product_id,
            'product_name' => json_decode($p->name)->{$locale} ?? $p->name,
            'total_quantity_sold' => (int)$p->total_quantity,
            'total_sales_amount' => round($p->total_sales, 2),
        ])->toArray();
    }
}
