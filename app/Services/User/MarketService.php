<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketService
{
    protected $affiliate;

    public function __construct()
    {
        $this->affiliate = auth('user')->user();
    }

    // ===============================
    // إحصائيات المسوّق
    // ===============================
    public function getStatistics(): array
    {
        $affiliate = auth('user')->user();

        $orders = Order::where('affiliate_id', $affiliate->affiliate_id)->get();

        // ===============================
        // الطلبات
        // ===============================
        $totalOrdersCount = $orders->count();

        $deliveredOrders = $orders->where('status', OrderStatus::DELIVERED->value);

        // ===============================
        // المبيعات
        // ===============================
        $totalSales = $orders->sum('total');

        // ===============================
        // العمولة المكتسبة (طلبات مكتملة فقط)
        // ===============================
        $earnedCommission = $deliveredOrders->sum('affiliate_commission');

        // ===============================
        // الأرباح المعلّقة (طلبات غير مكتملة)
        // ===============================
        $pendingCommission = $orders
            ->where('status', '!=', OrderStatus::DELIVERED->value)
            ->sum('affiliate_commission');

        // ===============================
        // المسحوبات
        // ===============================
        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum('amount');

        $availableBalance = $earnedCommission - $totalWithdrawn;

        return [
            'affiliate' => [
                'affiliate_id' => $affiliate->affiliate_id,
                'coupon_code'  => $affiliate->coupon_id,
                'rate'         => $affiliate->affiliate_rate,
            ],
            'stats' => [
                'total_orders'      => $totalOrdersCount,
                'delivered_orders'  => $deliveredOrders->count(),
                'total_sales'       => round($totalSales, 2),

                'earned_commission' => round($earnedCommission, 2),
                'pending_earnings'  => round($pendingCommission, 2),

                'withdrawn'         => round($totalWithdrawn, 2),
                'available_balance' => round($availableBalance, 2),
            ],
        ];
    }

    // ===============================
    // جلب الطلبات للمسوّق
    // ===============================
    public function getOrders(array $filters, int $perPage): array
    {
        $affiliate = auth('user')->user();

        $query = Order::query()
            ->where('affiliate_id', $affiliate->affiliate_id)
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

        // ناخد نسخة بدون paginate عشان نحسب summary
        $ordersCollection = (clone $query)->get();

        $deliveredOrders = $ordersCollection
            ->where('status', OrderStatus::DELIVERED->value);

        $summary = [
            'total_orders'      => $ordersCollection->count(),
            'delivered_orders'  => $deliveredOrders->count(),
            'total_sales'       => round($ordersCollection->sum('total'), 2),
            'earned_commission' => round($deliveredOrders->sum('affiliate_commission'), 2),
            'pending_earnings'  => round(
                $ordersCollection
                    ->where('status', '!=', OrderStatus::DELIVERED->value)
                    ->sum('affiliate_commission'),
                2
            ),
        ];

        $paginatedOrders = $query->paginate($perPage);

        return [
            'orders' => $paginatedOrders,
            'summary' => $summary,
        ];
    }



    // ===============================
    // العمليات المالية
    // ===============================
    public function getTransactions(array $filters, int $perPage): array
    {
        $query = AffiliateWalletTransaction::where(
            'affiliate_id',
            $this->affiliate->affiliate_id
        );

        // ===============================
        // الفلاتر
        // ===============================

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

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

        // ===============================
        // Summary (بدون paginate)
        // ===============================
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

        $summary = [
            'transactions_count' => $collection->count(),
            'total_commissions'  => round($totalCommissions, 2),
            'total_withdrawn'    => round($totalWithdrawn, 2),
            'pending_withdrawals' => round($pendingWithdrawals, 2),
            'available_balance'  => round($totalCommissions - $totalWithdrawn, 2),
        ];

        $paginated = $query->paginate($perPage);

        return [
            'transactions' => $paginated,
            'summary' => $summary,
        ];
    }


    // ===============================
    // إنشاء طلب سحب
    // ===============================
    public function createWithdraw(float $amount): ?AffiliateWithdrawRequest
    {
        $totalCommission = AffiliateWalletTransaction::where('affiliate_id', $this->affiliate->affiliate_id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $this->affiliate->affiliate_id)
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum('amount');

        $availableBalance = $totalCommission - $totalWithdrawn;

        if ($amount > $availableBalance) {
            return null;
        }

        return AffiliateWithdrawRequest::create([
            'affiliate_id' => $this->affiliate->affiliate_id,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    // ===============================
    // جلب طلبات السحب السابقة
    // ===============================
    public function getWithdrawRequests(int $perPage): LengthAwarePaginator
    {
        return AffiliateWithdrawRequest::where('affiliate_id', $this->affiliate->affiliate_id)
            ->latest()
            ->paginate($perPage);
    }
}
