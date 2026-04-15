<?php

namespace App\Services\Admin;

use App\Models\AffiliateWithdrawRequest;
use App\Models\AffiliateWalletTransaction;
use App\Services\BaseService;
use App\Http\Resources\Admin\AffiliateWithdrawRequest\AllResource;
use App\Http\Resources\Admin\AffiliateWithdrawRequest\OneResource;
use App\Exceptions\CustomExceptionWithMessage;
use App\Services\Base\NotificationService;
use Illuminate\Support\Facades\DB;

class AffiliateWithdrawRequestService extends BaseService
{
    protected $model = AffiliateWithdrawRequest::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['affiliate'];
    protected $searchableFields = ['id', 'affiliate_id', 'status'];
    protected $sortableFields = ['id', 'created_at', 'amount', 'status'];

    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }
    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        if (!empty($filters['affiliate_id'])) {
            $query->where('affiliate_id', $filters['affiliate_id']);
            unset($filters['affiliate_id']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
            unset($filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
            unset($filters['to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
            unset($filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
            unset($filters['max_amount']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $request = AffiliateWithdrawRequest::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw new CustomExceptionWithMessage('custom.withdrawals.only_pending_can_be_updated');
            }

            $newStatus = $data['status'] ?? null;
            if (!in_array($newStatus, ['approved', 'rejected'], true)) {
                throw new CustomExceptionWithMessage('custom.withdrawals.invalid_status');
            }

            if ($newStatus === 'approved') {
                $this->ensureAvailableBalance($request->affiliate_id, (float) $request->amount);

                AffiliateWalletTransaction::create([
                    'affiliate_id' => $request->affiliate_id,
                    'type' => 'withdraw',
                    'amount' => $request->amount,
                    'order_id' => null,
                ]);
            }

            $request->update([
                'status' => $newStatus,
                'note' => $data['note'] ?? $request->note,
            ]);

            $title = $newStatus === 'approved'
                ? '✅ تمت الموافقة على طلب السحب'
                : '❌ تم رفض طلب السحب';

            $message = $newStatus === 'approved'
                ? "تمت الموافقة على طلب السحب الخاص بك بقيمة {$request->amount}."
                : "تم رفض طلب السحب بقيمة {$request->amount}.\nالسبب: " . ($data['note'] ?? 'غير محدد');



            $this->notificationService->send(
                $request->affiliate,
                $title,
                $message,
                [
                    'type' => 'admin'
                ]
            );

            return new $this->resource($request->fresh(['affiliate']));
        });
    }

    protected function ensureAvailableBalance(string $affiliateId, float $amount): void
    {
        $totalCommission = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->whereIn('type', ['commission', 'visit_commission'])
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliateId)
            ->where('type', 'withdraw')
            ->sum('amount');

        $available = $totalCommission - $totalWithdrawn;

        if ($amount > $available) {
            throw new CustomExceptionWithMessage('custom.withdrawals.amount_exceeds_balance');
        }
    }
}
