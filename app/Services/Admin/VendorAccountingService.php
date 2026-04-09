<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Models\Vendor;
use App\Models\VendorWithdrawRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorAccountingService
{
    public function getSummary(array $filters = []): array
    {
        $vendorIds = Vendor::query()->pluck('id')->all();

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

        $vendors = Vendor::query()
            ->select('id', 'commission_rate', 'is_active')
            ->get()
            ->keyBy('id');

        $salesRows = $this->aggregateSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $withdrawRows = $this->aggregateWithdrawalsForVendors($vendorIds, $filters)->keyBy('vendor_id');

        $grossSales = 0.0;
        $platformCommission = 0.0;
        $discountsShare = 0.0;
        $refunds = 0.0;
        $netDue = 0.0;
        $paid = 0.0;
        $pendingWithdrawals = 0.0;

        foreach ($vendors as $vendorId => $vendor) {
            $statement = $this->buildWalletStatement(
                commissionRate: (float) ($vendor->commission_rate ?? 0),
                salesRow: (array) ($salesRows[$vendorId] ?? []),
                withdrawRow: (array) ($withdrawRows[$vendorId] ?? [])
            );

            $grossSales += $statement['gross_sales'];
            $platformCommission += $statement['platform_commission'];
            $discountsShare += $statement['discounts_share'];
            $refunds += $statement['refunds'];
            $netDue += $statement['net_due'];
            $paid += $statement['paid'];
            $pendingWithdrawals += $statement['pending_withdrawals'];
        }

        $remainingAfterPaid = $netDue - $paid;
        $availableForWithdraw = $remainingAfterPaid - $pendingWithdrawals;

        return [
            'vendors_count' => $vendors->count(),
            'active_vendors_count' => $vendors->where('is_active', true)->count(),
            'gross_sales' => $this->money($grossSales),
            'platform_commission' => $this->money($platformCommission),
            'discounts_share' => $this->money($discountsShare),
            'refunds' => $this->money($refunds),
            'net_due' => $this->money($netDue),
            'paid' => $this->money($paid),
            'pending_withdrawals' => $this->money($pendingWithdrawals),
            'remaining_after_paid' => $this->money($remainingAfterPaid),
            'available_for_withdraw' => $this->money($availableForWithdraw),
        ];
    }

    public function getVendorsAccounting(array $filters = [], int $perPage = 10): array
    {
        $query = Vendor::query()
            ->select('id', 'name', 'owner_name', 'commission_rate', 'is_active', 'created_at');

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

        $vendors = $query->orderByDesc('id')->paginate($perPage);
        $vendorIds = collect($vendors->items())->pluck('id')->all();

        $salesRows = $this->aggregateSalesForVendors($vendorIds, $filters)->keyBy('vendor_id');
        $withdrawRows = $this->aggregateWithdrawalsForVendors($vendorIds, $filters)->keyBy('vendor_id');

        $items = collect($vendors->items())->map(function (Vendor $vendor) use ($salesRows, $withdrawRows) {
            $statement = $this->buildWalletStatement(
                commissionRate: (float) ($vendor->commission_rate ?? 0),
                salesRow: (array) ($salesRows[$vendor->id] ?? []),
                withdrawRow: (array) ($withdrawRows[$vendor->id] ?? [])
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
        $withdrawRow = (array) $this->aggregateWithdrawalsForVendors([$vendorId], $filters)->first();

        $statement = $this->buildWalletStatement(
            commissionRate: (float) ($vendor->commission_rate ?? 0),
            salesRow: $salesRow,
            withdrawRow: $withdrawRow
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

        $extrasSumExpression = '0';
        if (Schema::hasTable('order_item_extras')) {
            $extrasSubQuery = DB::table('order_item_extras')
                ->selectRaw('order_item_id, SUM(price) as extras_total')
                ->groupBy('order_item_id');

            $query->leftJoinSub($extrasSubQuery, 'oie', function ($join) {
                $join->on('oie.order_item_id', '=', 'oi.id');
            });

            $extrasSumExpression = 'COALESCE(oie.extras_total, 0)';
        }

        $lineGrossExpression = "((oi.price * oi.quantity) + ({$extrasSumExpression} * oi.quantity))";
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

    private function buildWalletStatement(float $commissionRate, array $salesRow = [], array $withdrawRow = []): array
    {
        $ordersCount = (int) ($salesRow['orders_count'] ?? 0);
        $grossSales = (float) ($salesRow['gross_sales'] ?? 0);
        $discountsShare = (float) ($salesRow['discounts_share'] ?? 0);
        $refunds = 0.0;

        $platformCommission = $grossSales * ($commissionRate / 100);
        $netDue = $grossSales - $platformCommission - $discountsShare - $refunds;

        $paid = (float) ($withdrawRow['paid_amount'] ?? 0);
        $pendingWithdrawals = (float) ($withdrawRow['pending_amount'] ?? 0);
        $remainingAfterPaid = $netDue - $paid;
        $availableForWithdraw = $remainingAfterPaid - $pendingWithdrawals;

        return [
            'orders_count' => $ordersCount,
            'commission_rate' => $this->money($commissionRate),
            'gross_sales' => $this->money($grossSales),
            'platform_commission' => $this->money($platformCommission),
            'discounts_share' => $this->money($discountsShare),
            'refunds' => $this->money($refunds),
            'net_due' => $this->money($netDue),
            'paid' => $this->money($paid),
            'pending_withdrawals' => $this->money($pendingWithdrawals),
            'remaining_after_paid' => $this->money($remainingAfterPaid),
            'available_for_withdraw' => $this->money($availableForWithdraw),
        ];
    }

    private function serializeVendor(Vendor $vendor): array
    {
        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'name_translations' => $vendor->getTranslations('name'),
            'owner_name' => $vendor->owner_name,
            'commission_rate' => $this->money((float) ($vendor->commission_rate ?? 0)),
            'is_active' => (bool) $vendor->is_active,
            'created_at' => $vendor->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}
