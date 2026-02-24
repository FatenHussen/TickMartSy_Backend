<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Http\Resources\Coupon\OneResource;
use App\Models\Order;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Coupon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        // $affiliate = auth('user')->user();

        $orders = Order::where('affiliate_id', $this->affiliate->affiliate_id)->get();

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
        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $this->affiliate->affiliate_id)
            ->where('type', 'withdraw')
            ->sum('amount');

        $availableBalance = $earnedCommission - $totalWithdrawn;

        $activeCoupon = Coupon::where('affiliate_id', $this->affiliate->affiliate_id)
            ->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereColumn('used_count', '<', 'max_uses')
            ->first();

        return [
            'affiliate' => [
                'affiliate_id' => $this->affiliate->affiliate_id,
                'coupon_code'  =>  $activeCoupon  ? OneResource::make($activeCoupon) : null,
                'rate'         => $this->affiliate->affiliate_rate,
            ],
            'stats' => [
                'total_visites' => $this->affiliate->affiliate_visits,
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

        // if (!empty($filters['status'])) {
        //     $query->where('status', $filters['status']);
        // }

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
    public function getWithdrawRequests(array $filters, int $perPage): array
    {
        $query = AffiliateWithdrawRequest::where('affiliate_id', $this->affiliate->affiliate_id);

        // ===============================
        // تطبيق الفلاتر
        // ===============================
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
        // Summary (الإحصائيات بدون Pagination)
        // ===============================
        $collection = (clone $query)->get();

        $summary = [
            'total_requests' => $collection->count(),
            'total_withdrawn' => round($collection->where('status', 'completed')->sum('amount'), 2),
            'pending_amount' => round($collection->where('status', 'pending')->sum('amount'), 2),
        ];

        // ===============================
        // Paginate
        // ===============================
        $paginated = $query->paginate($perPage);

        return [
            'withdraw_requests' => $paginated,
            'summary' => $summary,
        ];
    }

    public function getMonthlyOrdersSummary(int $year = null): array
    {
        $year = $year ?? now()->year;
        $affiliateId = $this->affiliate->affiliate_id;

        // جلب البيانات مجمعة حسب الشهر مباشرة من قاعدة البيانات
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

        // تحويل النتائج لمصفوفة لكل شهر
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
