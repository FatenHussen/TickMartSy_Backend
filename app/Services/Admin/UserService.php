<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\EndUser\AllResource;
use App\Http\Resources\EndUser\OneResource;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Coupon;
use App\Models\User;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{
    private const COMMISSION_TYPES = [
        'percentage_order',
        'fixed_per_order',
        'percentage_selected_products',
    ];

    public function __construct(User $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['area', 'addresses.area', 'marketerCoupon'];
        $this->searchableFields = ['id', 'name', 'code'];
    }

    public function create($data)
    {
        $hasAffiliateData =
            !empty($data['is_affiliate']) ||
            array_key_exists('affiliate_id', $data) ||
            array_key_exists('affiliate_rate', $data) ||
            array_key_exists('affiliate_commission_type', $data) ||
            array_key_exists('affiliate_fixed_commission', $data) ||
            array_key_exists('affiliate_product_ids', $data) ||
            array_key_exists('affiliate_visit_commission_enabled', $data) ||
            array_key_exists('affiliate_visit_commission_threshold', $data) ||
            array_key_exists('affiliate_visit_commission_amount', $data);

        if (!empty($data['affiliate_id']) && (!empty($data['affiliate_rate']) || !empty($data['affiliate_fixed_commission']))) {
            $data['is_affiliate'] = true;
            $data['affiliate_approved'] = true;
        }

        if ($hasAffiliateData) {
            $this->normalizeAffiliatePayload($data);
            $this->normalizeVisitCommissionPayload($data);
        }

        $createResult = parent::create($data);
        $user = $this->extractUserModelFromServiceResult($createResult);

        if ($hasAffiliateData && $user) {
            $this->syncAffiliateProducts($user, $data);
        }

        return $createResult;
    }

    public function update($id, array $data)
    {
        $object = $this->model::findOrFail($id);

        $hasAffiliatePayload =
            array_key_exists('affiliate_commission_type', $data) ||
            array_key_exists('affiliate_fixed_commission', $data) ||
            array_key_exists('affiliate_product_ids', $data) ||
            array_key_exists('affiliate_visit_commission_enabled', $data) ||
            array_key_exists('affiliate_visit_commission_threshold', $data) ||
            array_key_exists('affiliate_visit_commission_amount', $data);

        $hasAffiliateData =
            array_key_exists('affiliate_id', $data) ||
            array_key_exists('affiliate_rate', $data) ||
            $hasAffiliatePayload;

        if ($hasAffiliateData) {
            if (!$object->is_affiliate) {
                throw new CustomExceptionWithMessage('custom.marketer.request_not_submitted');
            }

            if (
                array_key_exists('affiliate_id', $data) &&
                $object->affiliate_id &&
                $object->affiliate_id != $data['affiliate_id']
            ) {
                throw new CustomExceptionWithMessage('custom.marketer.cannot_change_number');
            }

            $this->normalizeAffiliatePayload($data, $object);
            $this->normalizeVisitCommissionPayload($data, $object, true);

            if ($this->hasEffectiveCommissionConfig($data, $object)) {
                $data['affiliate_approved'] = true;
                (new NotificationService)->send(
                    $object,
                    'قبول طلبك ك مسوّق',
                    'تم قبول طلبك ك مسوق من قبل الادمن ',
                    [
                        'type' => 'markter'
                    ]
                );
            }
        }

        $updateResult = parent::update($id, $data);
        $updatedUser = $this->extractUserModelFromServiceResult($updateResult);

        if ($updatedUser) {
            $this->syncAffiliateProducts($updatedUser, $data);
        }

        return $updateResult;
    }

    public function markters()
    {
        $users = User::where('affiliate_approved', true)
            ->select('id', 'affiliate_id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->affiliate_id,
                    'label' => $user->id . '-' . $user->affiliate_id . '-' . $user->name,
                ];
            });

        return $users;
    }

    public function demoteAffiliate(int $id): User
    {
        return DB::transaction(function () use ($id) {
            $user = $this->model::findOrFail($id);

            if (empty($user->affiliate_id)) {
                throw new CustomExceptionWithMessage('custom.marketer.no_affiliate_number');
            }

            $hasPendingWithdrawRequests = AffiliateWithdrawRequest::where('affiliate_id', $user->affiliate_id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingWithdrawRequests) {
                throw new CustomExceptionWithMessage('custom.marketer.pending_withdraw_requests');
            }

            Coupon::where('affiliate_id', $user->affiliate_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $user->affiliateProducts()->sync([]);

            $user->update([
                'is_affiliate' => false,
                'affiliate_approved' => false,
                'affiliate_rate' => null,
                'affiliate_commission_type' => 'percentage_order',
                'affiliate_fixed_commission' => null,
                'affiliate_visit_commission_enabled' => false,
                'affiliate_visit_commission_threshold' => null,
                'affiliate_visit_commission_amount' => null,
                'affiliate_visit_rewarded_steps' => 0,
            ]);

            (new NotificationService)->send(
                $user,
                'إيقاف حساب التسويق',
                'تم تحويل حسابك إلى حساب مستخدم عادي من قبل الإدارة.',
                [
                    'type' => 'markter'
                ]
            );

            return $user->fresh();
        });
    }

    public function reactivateAffiliate(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->model::findOrFail($id);

            $affiliateId = $data['affiliate_id'] ?? $user->affiliate_id;

            if (empty($affiliateId)) {
                throw new CustomExceptionWithMessage('custom.marketer.no_affiliate_number');
            }

            $isAffiliateIdTaken = $this->model::query()
                ->where('affiliate_id', $affiliateId)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($isAffiliateIdTaken) {
                throw new CustomExceptionWithMessage('custom.marketer.affiliate_number_taken');
            }

            $data['affiliate_id'] = $affiliateId;
            $data['is_affiliate'] = true;

            $this->normalizeAffiliatePayload($data, $user);
            $this->normalizeVisitCommissionPayload($data, $user, true);
            $data['affiliate_approved'] = true;

            $user->update([
                'is_affiliate' => true,
                'affiliate_approved' => true,
                'affiliate_id' => $data['affiliate_id'],
                'affiliate_rate' => $data['affiliate_rate'] ?? null,
                'affiliate_commission_type' => $data['affiliate_commission_type'],
                'affiliate_fixed_commission' => $data['affiliate_fixed_commission'] ?? null,
                'affiliate_visit_commission_enabled' => $data['affiliate_visit_commission_enabled'] ?? false,
                'affiliate_visit_commission_threshold' => $data['affiliate_visit_commission_threshold'] ?? null,
                'affiliate_visit_commission_amount' => $data['affiliate_visit_commission_amount'] ?? null,
                'affiliate_visit_rewarded_steps' => $data['affiliate_visit_rewarded_steps'] ?? $user->affiliate_visit_rewarded_steps,
            ]);

            $this->syncAffiliateProducts($user, $data);

            (new NotificationService)->send(
                $user,
                'إعادة تفعيل حساب التسويق',
                'تم إعادة تفعيل حسابك كمسوّق من قبل الإدارة.',
                [
                    'type' => 'markter'
                ]
            );

            return $user->fresh();
        });
    }

    private function normalizeAffiliatePayload(array &$data, ?User $existingUser = null): void
    {
        if (!array_key_exists('affiliate_commission_type', $data)) {
            if ($existingUser) {
                $data['affiliate_commission_type'] = $existingUser->affiliate_commission_type ?? 'percentage_order';
            } else {
                $data['affiliate_commission_type'] = $data['affiliate_commission_type'] ?? 'percentage_order';
            }
        }

        $type = $data['affiliate_commission_type'];

        if (!in_array($type, self::COMMISSION_TYPES, true)) {
            throw new CustomExceptionWithMessage('custom.marketer.invalid_commission_type');
        }

        if ($type === 'fixed_per_order') {
            $fixedAmount = $data['affiliate_fixed_commission'] ?? $existingUser?->affiliate_fixed_commission;

            if ($fixedAmount === null) {
                throw new CustomExceptionWithMessage('custom.marketer.fixed_amount_required');
            }

            $data['affiliate_rate'] = null;
        } else {
            $rate = $data['affiliate_rate'] ?? $existingUser?->affiliate_rate;

            if ($rate === null) {
                throw new CustomExceptionWithMessage('custom.marketer.rate_required_for_percentage');
            }

            $data['affiliate_fixed_commission'] = null;
        }

        if ($type === 'percentage_selected_products') {
            $existingProductIds = $existingUser
                ? $existingUser->affiliateProducts()->pluck('products.id')->all()
                : [];

            $productIds = $data['affiliate_product_ids'] ?? $existingProductIds;

            if (empty($productIds)) {
                throw new CustomExceptionWithMessage('custom.marketer.products_required_for_selected_percentage');
            }
        } elseif (array_key_exists('affiliate_product_ids', $data) && empty($data['affiliate_product_ids'])) {
            // Explicit empty payload for other types should clear previous selections.
            $data['affiliate_product_ids'] = [];
        }
    }

    private function syncAffiliateProducts(User $user, array $data): void
    {
        if (array_key_exists('affiliate_product_ids', $data)) {
            $user->affiliateProducts()->sync($data['affiliate_product_ids'] ?? []);
        }
    }

    private function normalizeVisitCommissionPayload(
        array &$data,
        ?User $existingUser = null,
        bool $resetStepsWhenConfigChanges = false
    ): void {
        $enabled = array_key_exists('affiliate_visit_commission_enabled', $data)
            ? (bool) $data['affiliate_visit_commission_enabled']
            : (bool) ($existingUser?->affiliate_visit_commission_enabled ?? false);

        $data['affiliate_visit_commission_enabled'] = $enabled;

        if (!$enabled) {
            $data['affiliate_visit_commission_threshold'] = null;
            $data['affiliate_visit_commission_amount'] = null;
            $data['affiliate_visit_rewarded_steps'] = 0;
            return;
        }

        $threshold = $data['affiliate_visit_commission_threshold']
            ?? $existingUser?->affiliate_visit_commission_threshold;
        $amount = $data['affiliate_visit_commission_amount']
            ?? $existingUser?->affiliate_visit_commission_amount;

        if (!$threshold) {
            throw new CustomExceptionWithMessage('custom.marketer.visit_threshold_required');
        }

        if (!$amount) {
            throw new CustomExceptionWithMessage('custom.marketer.visit_amount_required');
        }

        $threshold = (int) $threshold;
        $amount = (float) $amount;

        $data['affiliate_visit_commission_threshold'] = $threshold;
        $data['affiliate_visit_commission_amount'] = $amount;

        $shouldResetSteps = !$existingUser
            || !$existingUser->affiliate_visit_commission_enabled
            || $resetStepsWhenConfigChanges
            || $existingUser->affiliate_visit_commission_threshold != $threshold
            || (float) $existingUser->affiliate_visit_commission_amount != $amount;

        if ($shouldResetSteps) {
            $currentVisits = (int) ($existingUser?->affiliate_visits ?? 0);
            $data['affiliate_visit_rewarded_steps'] = intdiv($currentVisits, $threshold);
        }
    }

    private function hasEffectiveCommissionConfig(array $data, User $user): bool
    {
        $type = $data['affiliate_commission_type'] ?? $user->affiliate_commission_type ?? 'percentage_order';

        if ($type === 'fixed_per_order') {
            $fixedAmount = $data['affiliate_fixed_commission'] ?? $user->affiliate_fixed_commission;
            return $fixedAmount !== null && (float) $fixedAmount >= 0;
        }

        $rate = $data['affiliate_rate'] ?? $user->affiliate_rate;
        return $rate !== null && (float) $rate > 0;
    }

    private function extractUserModelFromServiceResult(mixed $result): ?User
    {
        if ($result instanceof User) {
            return $result;
        }

        if (is_object($result) && property_exists($result, 'resource') && $result->resource instanceof User) {
            return $result->resource;
        }

        return null;
    }
}
