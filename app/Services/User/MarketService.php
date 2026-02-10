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
    public function getOrders(int $perPage): LengthAwarePaginator
    {
        return Order::where('affiliate_id', $this->affiliate->affiliate_id)
            ->latest()
            ->paginate($perPage);
    }

    // ===============================
    // العمليات المالية
    // ===============================
    public function getTransactions(int $perPage): LengthAwarePaginator
    {
        return AffiliateWalletTransaction::where('affiliate_id', $this->affiliate->affiliate_id)
            ->latest()
            ->paginate($perPage);
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
