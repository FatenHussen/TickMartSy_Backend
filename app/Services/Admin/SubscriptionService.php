<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Subscription\AllResource;
use App\Http\Resources\Admin\Subscription\OneResource;
use App\Models\Subscription;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class SubscriptionService extends BaseService
{
    protected $model = Subscription::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'user',
        'package',
        'paymentMethod',
    ];

    protected $searchableFields = [
        'id',
        'status',
    ];

    protected $sortableFields = [
        'id',
        'start_date',
        'end_date',
        'created_at',
        'status',
    ];

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $subscription = $this->applyAdminCityRestriction(Subscription::query())
                ->with('package')
                ->findOrFail($id);

            $originalStatus = $subscription->status;

            if (($data['status'] ?? null) === 'active' && $originalStatus === 'pending') {
                $package = $subscription->package;

                if ($package) {
                    $data['start_date'] = now()->toDateString();
                    $data['end_date'] = now()->addDays($package->duration_days)->toDateString();
                    $data['remaining_orders'] = $package->monthly_orders_limit;
                    $data['remaining_free_deliveries'] = $package->free_delivery_count;
                }
            }

            $subscription->update($data);
            $subscription->refresh();

            if ($originalStatus === 'pending' && $subscription->status === 'active') {
                $package = $subscription->package;

                if ($package && $package->points_bonus > 0) {
                    $pointService = app(\App\Services\PointService::class);

                    $pointService->addPointsToWallet(
                        userId: $subscription->user_id,
                        points: $package->points_bonus,
                        ruleId: null,
                        source: 'subscription_package',
                        status: 'earned',
                        referenceType: 'subscription',
                        referenceId: $subscription->id,
                        expiresAfterDays: 365,
                        reason: "Package subscription bonus: {$package->name}"
                    );
                }
            }

            return new $this->resource($subscription);
        });
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
            unset($filters['user_id']);
        }

        // Filter by package_id
        if (!empty($filters['package_id'])) {
            $query->where('package_id', $filters['package_id']);
            unset($filters['package_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        // Filter by date range
        if (!empty($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
            unset($filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
            unset($filters['start_date_to']);
        }

        // Apply parent query builder
        return parent::queryBuilder($query, $filters, $config);
    }
}
