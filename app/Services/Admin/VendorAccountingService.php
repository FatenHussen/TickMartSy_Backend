<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Models\VendorWithdrawRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorAccountingService
{
    public function getSummary(array $filters = []): array
    {
        $vendorsQuery = Vendor::query()->select('id', 'settlement_cycle', 'is_active');

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $vendorsQuery->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['settlement_cycle'])) {
            $vendorsQuery->where('settlement_cycle', $filters['settlement_cycle']);
        }

        $vendorIds = (clone $vendorsQuery)->pluck('id')->all();

        if (empty($vendorIds)) {
            return [
                'vendors_count' => 0,
                'active_vendors_count' => 0,
                'gross_sales' => 0.0,
                'platform_commission' => 0.0,
                'discounts_share' => 0.0,
                'refunds' => 0.0,
                'net_due' => 0.0,
                'paid' => 0.0,
                'pending_withdrawals' => 0.0,
                'remaining_after_paid' => 0.0,
                'available_for_withdraw' => 0.0,
            ];
        }

        $vendors = $vendorsQuery->get()->keyBy('id');

        $salesRows = $this->aggregateSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $pendingSalesRows = $this->aggregatePendingSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $withdrawRows = $this->aggregateWithdrawalsForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $commissionProfiles = $this->resolveCommissionProfiles($vendors->values());

        $grossSales = 0.0;
        $pendingOrdersGrossSales = 0.0;
        $platformCommission = 0.0;
        $discountsShare = 0.0;
        $refunds = 0.0;
        $netDue = 0.0;
        $paid = 0.0;
        $pendingWithdrawals = 0.0;
        $paidRequestsCount = 0;
        $pendingRequestsCount = 0;
        $rejectedRequestsCount = 0;

        foreach ($vendors as $vendorId => $vendor) {
            $commissionProfile = $commissionProfiles->get($vendorId, [
                'commission_type' => 'percentage',
                'commission_rate' => 0.0,
                'fixed_commission' => 0.0,
                'source' => 'package',
                'source_package_id' => null,
                'source_package_name' => null,
            ]);

            $statement = $this->buildWalletStatement(
                commissionType: (string) ($commissionProfile['commission_type'] ?? 'percentage'),
                commissionRate: (float) ($commissionProfile['commission_rate'] ?? 0),
                fixedCommission: (float) ($commissionProfile['fixed_commission'] ?? 0),
                salesRow: (array) ($salesRows[$vendorId] ?? []),
                withdrawRow: (array) ($withdrawRows[$vendorId] ?? []),
                pendingSalesRow: (array) ($pendingSalesRows[$vendorId] ?? []),
                commissionMeta: [
                    'source' => (string) ($commissionProfile['source'] ?? 'vendor'),
                    'source_package_id' => $commissionProfile['source_package_id'] ?? null,
                    'source_package_name' => $commissionProfile['source_package_name'] ?? null,
                ]
            );

            $grossSales += $statement['gross_sales'];
            $pendingOrdersGrossSales += $statement['pending_orders_gross_sales'];
            $platformCommission += $statement['platform_commission'];
            $discountsShare += $statement['discounts_share'];
            $refunds += $statement['refunds'];
            $netDue += $statement['net_due'];
            $paid += $statement['paid'];
            $pendingWithdrawals += $statement['pending_withdrawals'];
            $paidRequestsCount += (int) ($statement['paid_requests_count'] ?? 0);
            $pendingRequestsCount += (int) ($statement['pending_requests_count'] ?? 0);
            $rejectedRequestsCount += (int) ($statement['rejected_requests_count'] ?? 0);
        }

        $remainingAfterPaid = $netDue - $paid;
        $availableForWithdraw = $remainingAfterPaid - $pendingWithdrawals;

        return [
            'vendors_count' => $vendors->count(),
            'active_vendors_count' => $vendors->where('is_active', true)->count(),
            'gross_sales' => $this->money($grossSales),
            'pending_orders_gross_sales' => $this->money($pendingOrdersGrossSales),
            'platform_commission' => $this->money($platformCommission),
            'discounts_share' => $this->money($discountsShare),
            'refunds' => $this->money($refunds),
            'net_due' => $this->money($netDue),
            'paid' => $this->money($paid),
            'pending_withdrawals' => $this->money($pendingWithdrawals),
            'paid_requests_count' => $paidRequestsCount,
            'pending_requests_count' => $pendingRequestsCount,
            'rejected_requests_count' => $rejectedRequestsCount,
            'remaining_after_paid' => $this->money($remainingAfterPaid),
            'available_for_withdraw' => $this->money($availableForWithdraw),
        ];
    }

    public function getVendorsAccounting(array $filters = [], int $perPage = 10): array
    {
        $query = Vendor::query()
            ->select('id', 'name', 'owner_name', 'settlement_cycle', 'is_active', 'created_at');

        if (!empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(owner_name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['settlement_cycle'])) {
            $query->where('settlement_cycle', $filters['settlement_cycle']);
        }

        $vendors = $query->orderByDesc('id')->paginate($perPage);
        $vendorIds = collect($vendors->items())->pluck('id')->all();

        $salesRows = $this->aggregateSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $pendingSalesRows = $this->aggregatePendingSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $withdrawRows = $this->aggregateWithdrawalsForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $commissionProfiles = $this->resolveCommissionProfiles(collect($vendors->items()));

        $items = collect($vendors->items())->map(function (Vendor $vendor) use ($salesRows, $withdrawRows, $pendingSalesRows, $commissionProfiles) {
            $commissionProfile = $commissionProfiles->get($vendor->id, [
                'commission_type' => 'percentage',
                'commission_rate' => 0.0,
                'fixed_commission' => 0.0,
                'source' => 'package',
                'source_package_id' => null,
                'source_package_name' => null,
            ]);

            $statement = $this->buildWalletStatement(
                commissionType: (string) ($commissionProfile['commission_type'] ?? 'percentage'),
                commissionRate: (float) ($commissionProfile['commission_rate'] ?? 0),
                fixedCommission: (float) ($commissionProfile['fixed_commission'] ?? 0),
                salesRow: (array) ($salesRows[$vendor->id] ?? []),
                withdrawRow: (array) ($withdrawRows[$vendor->id] ?? []),
                pendingSalesRow: (array) ($pendingSalesRows[$vendor->id] ?? []),
                commissionMeta: [
                    'source' => (string) ($commissionProfile['source'] ?? 'vendor'),
                    'source_package_id' => $commissionProfile['source_package_id'] ?? null,
                    'source_package_name' => $commissionProfile['source_package_name'] ?? null,
                ]
            );

            return [
                'vendor' => $this->serializeVendor($vendor),
                'wallet' => $statement,
            ];
        })->values()->all();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        ];
    }

    public function getVendorStatement(int $vendorId, array $filters = [], int $withdrawPerPage = 10): array
    {
        $vendor = Vendor::query()->findOrFail($vendorId);

        $salesRow = (array) $this->aggregateSalesForVendors([$vendorId], $filters)->first();
        $pendingSalesRow = (array) $this->aggregatePendingSalesForVendors([$vendorId], $filters)->first();
        $withdrawRow = (array) $this->aggregateWithdrawalsForVendors([$vendorId], $filters)->first();
        $commissionProfile = $this->resolveCommissionProfile($vendor);

        $statement = $this->buildWalletStatement(
            commissionType: (string) ($commissionProfile['commission_type'] ?? 'percentage'),
            commissionRate: (float) ($commissionProfile['commission_rate'] ?? 0),
            fixedCommission: (float) ($commissionProfile['fixed_commission'] ?? 0),
            salesRow: $salesRow,
            withdrawRow: $withdrawRow,
            pendingSalesRow: $pendingSalesRow,
            commissionMeta: [
                'source' => (string) ($commissionProfile['source'] ?? 'vendor'),
                'source_package_id' => $commissionProfile['source_package_id'] ?? null,
                'source_package_name' => $commissionProfile['source_package_name'] ?? null,
            ]
        );

        if (Schema::hasTable('vendor_withdraw_requests')) {
            $withdrawRequestsQuery = VendorWithdrawRequest::query()
                ->where('vendor_id', $vendorId)
                ->orderByDesc('created_at');

            if (!empty($filters['withdraw_status'])) {
                $withdrawRequestsQuery->where('status', $filters['withdraw_status']);
            }

            if (!empty($filters['from_date'])) {
                $withdrawRequestsQuery->whereDate(
                    DB::raw('COALESCE(requested_at, created_at)'),
                    '>=',
                    $filters['from_date']
                );
            }

            if (!empty($filters['to_date'])) {
                $withdrawRequestsQuery->whereDate(
                    DB::raw('COALESCE(requested_at, created_at)'),
                    '<=',
                    $filters['to_date']
                );
            }

            $withdrawRequests = $withdrawRequestsQuery->paginate($withdrawPerPage);
        } else {
            $withdrawRequests = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $withdrawPerPage, 1);
        }

        return [
            'vendor' => $this->serializeVendor($vendor),
            'wallet' => $statement,
            'withdraw_requests' => [
                'items' => collect($withdrawRequests->items())->map(function (VendorWithdrawRequest $request) {
                    return [
                        'id' => $request->id,
                        'amount' => $this->money((float) $request->amount),
                        'status' => $request->status,
                        'payment_method' => $request->payment_method,
                        'transfer_reference' => $request->transfer_reference,
                        'note' => $request->note,
                        'rejection_reason' => $request->rejection_reason,
                        'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
                        'processed_at' => $request->processed_at?->format('Y-m-d H:i:s'),
                        'created_at' => $request->created_at?->format('Y-m-d H:i:s'),
                    ];
                })->values()->all(),
                'pagination' => [
                    'current_page' => $withdrawRequests->currentPage(),
                    'last_page' => $withdrawRequests->lastPage(),
                    'per_page' => $withdrawRequests->perPage(),
                    'total' => $withdrawRequests->total(),
                ],
            ],
        ];
    }

    private function aggregateSalesForVendors(array $vendorIds, array $filters = []): Collection
    {
        if (empty($vendorIds)) {
            return collect();
        }

        $hasSnapshotAmount = Schema::hasColumn('order_items', 'commission_snapshot_amount');

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('shop_product_variants as spv', 'spv.id', '=', 'oi.shop_product_variant_id')
            ->join('shops as s', 's.id', '=', 'spv.shop_id')
            ->join('product_variants as pv', 'pv.id', '=', 'spv.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->whereIn('s.vendor_id', $vendorIds)
            ->where('o.status', OrderStatus::DELIVERED->value)
            ->whereNull('o.deleted_at');

        if (!empty($filters['from_date'])) {
            $query->whereDate(DB::raw('COALESCE(o.delivered_at, o.created_at)'), '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate(DB::raw('COALESCE(o.delivered_at, o.created_at)'), '<=', $filters['to_date']);
        }

        $lineGrossExpression = 'COALESCE(oi.total, 0)';
        $orderDiscountExpression = "(
            COALESCE(o.basket_discount, 0) +
            COALESCE(o.coupon_discount, 0) +
            COALESCE(o.coupon_discount_from_points, 0) +
            COALESCE(o.subscription_discount, 0) +
            COALESCE(o.promotion_discount, 0)
        )";
        $discountShareExpression = "(CASE
            WHEN COALESCE(o.subtotal, 0) > 0
                THEN {$orderDiscountExpression} * ({$lineGrossExpression} / o.subtotal)
            ELSE 0
        END)";

        return $query
            ->selectRaw('s.vendor_id')
            ->selectRaw('COUNT(DISTINCT oi.order_id) as orders_count')
            ->selectRaw("SUM({$lineGrossExpression}) as gross_sales")
            ->selectRaw("SUM({$discountShareExpression}) as discounts_share")
            ->selectRaw(
                $hasSnapshotAmount
                    ? 'SUM(COALESCE(oi.commission_snapshot_amount, 0)) as snapshot_platform_commission'
                    : '0 as snapshot_platform_commission'
            )
            ->groupBy('s.vendor_id')
            ->get();
    }

    private function aggregateWithdrawalsForVendors(array $vendorIds, array $filters = []): Collection
    {
        if (empty($vendorIds) || !Schema::hasTable('vendor_withdraw_requests')) {
            return collect();
        }

        $query = VendorWithdrawRequest::query()
            ->selectRaw('vendor_id')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount")
            ->selectRaw("COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_requests_count")
            ->selectRaw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests_count")
            ->selectRaw("COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_requests_count")
            ->whereIn('vendor_id', $vendorIds)
            ->groupBy('vendor_id');

        if (!empty($filters['from_date'])) {
            $query->whereDate(
                DB::raw('COALESCE(requested_at, created_at)'),
                '>=',
                $filters['from_date']
            );
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate(
                DB::raw('COALESCE(requested_at, created_at)'),
                '<=',
                $filters['to_date']
            );
        }

        return $query->get();
    }

    private function aggregatePendingSalesForVendors(array $vendorIds, array $filters = []): Collection
    {
        if (empty($vendorIds)) {
            return collect();
        }

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('shop_product_variants as spv', 'spv.id', '=', 'oi.shop_product_variant_id')
            ->join('shops as s', 's.id', '=', 'spv.shop_id')
            ->join('product_variants as pv', 'pv.id', '=', 'spv.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->whereIn('s.vendor_id', $vendorIds)
            ->whereIn('o.status', [
                OrderStatus::PENDING->value,
                OrderStatus::PREPARING->value,
                OrderStatus::OUT_DELIVERY->value,
            ])
            ->whereNull('o.deleted_at');

        if (!empty($filters['from_date'])) {
            $query->whereDate('o.created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('o.created_at', '<=', $filters['to_date']);
        }

        return $query
            ->selectRaw('s.vendor_id')
            ->selectRaw('COUNT(DISTINCT oi.order_id) as pending_orders_count')
            ->selectRaw('SUM(COALESCE(oi.total, 0)) as pending_orders_gross_sales')
            ->groupBy('s.vendor_id')
            ->get();
    }

    private function resolveCommissionProfiles(Collection $vendors): Collection
    {
        $vendors = $vendors->filter(fn ($vendor) => $vendor instanceof Vendor)->values();
        if ($vendors->isEmpty()) {
            return collect();
        }

        $vendorIds = $vendors->pluck('id')->all();
        $activeSubscriptions = $this->getActiveSubscriptionsForVendors($vendorIds);

        return $vendors->mapWithKeys(function (Vendor $vendor) use ($activeSubscriptions) {
            return [$vendor->id => $this->resolveCommissionProfile($vendor, $activeSubscriptions->get($vendor->id))];
        });
    }

    private function resolveCommissionProfile(Vendor $vendor, ?VendorSubscription $subscription = null): array
    {
        $defaultProfile = [
            'commission_type' => 'percentage',
            'commission_rate' => 0.0,
            'fixed_commission' => 0.0,
            'source' => 'package',
            'source_package_id' => null,
            'source_package_name' => null,
        ];

        $subscription ??= $this->getActiveSubscriptionForVendor($vendor->id);
        if (!$subscription || !$subscription->package) {
            return $defaultProfile;
        }

        $package = $subscription->package;
        $packageFixedCommission = (float) ($package->commission_per_order ?? 0);
        $packageCommissionType = $packageFixedCommission > 0 ? 'fixed' : 'percentage';

        return [
            'commission_type' => $packageCommissionType,
            'commission_rate' => $packageCommissionType === 'percentage'
                ? (float) ($package->commission_rate ?? 0)
                : 0.0,
            'fixed_commission' => $packageCommissionType === 'fixed'
                ? $packageFixedCommission
                : 0.0,
            'source' => 'package',
            'source_package_id' => $package->id,
            'source_package_name' => $package->name,
        ];
    }

    private function getActiveSubscriptionsForVendors(array $vendorIds): Collection
    {
        if (empty($vendorIds) || !Schema::hasTable('vendor_subscriptions')) {
            return collect();
        }

        $today = now()->toDateString();

        return VendorSubscription::query()
            ->with('package')
            ->whereIn('vendor_id', $vendorIds)
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today)
            ->orderByDesc('ends_at')
            ->get()
            ->groupBy('vendor_id')
            ->map(fn (Collection $subscriptions) => $subscriptions->first());
    }

    private function getActiveSubscriptionForVendor(int $vendorId): ?VendorSubscription
    {
        return $this->getActiveSubscriptionsForVendors([$vendorId])->get($vendorId);
    }

    private function buildWalletStatement(
        string $commissionType,
        float $commissionRate,
        float $fixedCommission,
        array $salesRow = [],
        array $withdrawRow = [],
        array $pendingSalesRow = [],
        array $commissionMeta = []
    ): array
    {
        $ordersCount = (int) ($salesRow['orders_count'] ?? 0);
        $grossSales = (float) ($salesRow['gross_sales'] ?? 0);
        $discountsShare = (float) ($salesRow['discounts_share'] ?? 0);
        $pendingOrdersCount = (int) ($pendingSalesRow['pending_orders_count'] ?? 0);
        $pendingOrdersGrossSales = (float) ($pendingSalesRow['pending_orders_gross_sales'] ?? 0);
        $refunds = 0.0;

        $platformCommission = $commissionType === 'fixed'
            ? $ordersCount * $fixedCommission
            : $grossSales * ($commissionRate / 100);

        $netDue = $grossSales - $platformCommission - $discountsShare - $refunds;

        $paid = (float) ($withdrawRow['paid_amount'] ?? 0);
        $pendingWithdrawals = (float) ($withdrawRow['pending_amount'] ?? 0);
        $paidRequestsCount = (int) ($withdrawRow['paid_requests_count'] ?? 0);
        $pendingRequestsCount = (int) ($withdrawRow['pending_requests_count'] ?? 0);
        $rejectedRequestsCount = (int) ($withdrawRow['rejected_requests_count'] ?? 0);
        $remainingAfterPaid = $netDue - $paid;
        $availableForWithdraw = $remainingAfterPaid - $pendingWithdrawals;

        return [
            'orders_count' => $ordersCount,
            'pending_orders_count' => $pendingOrdersCount,
            'commission_type' => $commissionType,
            'commission_rate' => $this->money($commissionRate),
            'fixed_commission' => $this->money($fixedCommission),
            'commission_source' => (string) ($commissionMeta['source'] ?? 'package'),
            'commission_source_package_id' => $commissionMeta['source_package_id'] ?? null,
            'commission_source_package_name' => $commissionMeta['source_package_name'] ?? null,
            'gross_sales' => $this->money($grossSales),
            'pending_orders_gross_sales' => $this->money($pendingOrdersGrossSales),
            'platform_commission' => $this->money($platformCommission),
            'discounts_share' => $this->money($discountsShare),
            'refunds' => $this->money($refunds),
            'net_due' => $this->money($netDue),
            'paid' => $this->money($paid),
            'pending_withdrawals' => $this->money($pendingWithdrawals),
            'paid_requests_count' => $paidRequestsCount,
            'pending_requests_count' => $pendingRequestsCount,
            'rejected_requests_count' => $rejectedRequestsCount,
            'remaining_after_paid' => $this->money($remainingAfterPaid),
            'available_for_withdraw' => $this->money($availableForWithdraw),
        ];
    }

    private function serializeVendor(Vendor $vendor): array
    {
        $settlementCycle = $vendor->settlement_cycle ?: 'monthly';

        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'name_translations' => $vendor->getTranslations('name'),
            'owner_name' => $vendor->owner_name,
            'commission_type' => 'percentage',
            'commission_rate' => $this->money(0.0),
            'fixed_commission' => $this->money(0.0),
            'settlement_cycle' => $settlementCycle,
            'next_settlement_at' => $this->nextSettlementAt($settlementCycle),
            'is_active' => (bool) $vendor->is_active,
            'created_at' => $vendor->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function nextSettlementAt(string $cycle): string
    {
        $now = Carbon::now();

        if ($cycle === 'weekly') {
            return $now->copy()->endOfWeek()->format('Y-m-d 23:59:59');
        }

        return $now->copy()->endOfMonth()->format('Y-m-d 23:59:59');
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}
