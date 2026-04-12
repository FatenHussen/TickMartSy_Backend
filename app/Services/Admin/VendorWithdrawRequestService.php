<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Admin\VendorWithdrawRequest\AllResource;
use App\Http\Resources\Admin\VendorWithdrawRequest\OneResource;
use App\Models\VendorWithdrawRequest;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class VendorWithdrawRequestService extends BaseService
{
    protected $model = VendorWithdrawRequest::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['vendor'];
    protected $searchableFields = ['id', 'vendor_id', 'status', 'transfer_reference'];
    protected $sortableFields = ['id', 'created_at', 'amount', 'status', 'processed_at'];

    public function __construct(private readonly VendorAccountingService $vendorAccountingService) {}

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
            unset($filters['vendor_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
            unset($filters['payment_method']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate(DB::raw('COALESCE(requested_at, created_at)'), '>=', $filters['from']);
            unset($filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate(DB::raw('COALESCE(requested_at, created_at)'), '<=', $filters['to']);
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
            /** @var VendorWithdrawRequest $request */
            $request = VendorWithdrawRequest::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw new CustomExceptionWithMessage('custom.withdrawals.only_pending_can_be_updated');
            }

            $newStatus = $data['status'] ?? null;
            if (!in_array($newStatus, ['paid', 'rejected'], true)) {
                throw new CustomExceptionWithMessage('custom.withdrawals.invalid_status');
            }

            if ($newStatus === 'paid') {
                $this->ensureAvailableBalance($request->vendor_id, (float) $request->amount);
            }

            $payload = [
                'status' => $newStatus,
                'note' => $data['note'] ?? $request->note,
                'processed_at' => now(),
            ];

            if ($newStatus === 'paid') {
                $payload['payment_method'] = $data['payment_method'] ?? $request->payment_method;
                $payload['transfer_reference'] = $data['transfer_reference'] ?? $request->transfer_reference;
                $payload['rejection_reason'] = null;
            } else {
                $payload['rejection_reason'] = $data['rejection_reason'] ?? $request->rejection_reason;
                $payload['payment_method'] = null;
                $payload['transfer_reference'] = null;
            }

            $request->update($payload);

            return new $this->resource($request->fresh(['vendor']));
        });
    }

    protected function ensureAvailableBalance(int $vendorId, float $amount): void
    {
        $statement = $this->vendorAccountingService->getVendorStatement($vendorId);
        $remainingAfterPaid = (float) data_get($statement, 'wallet.remaining_after_paid', 0);

        if ($amount > $remainingAfterPaid) {
            throw new CustomExceptionWithMessage('custom.withdrawals.amount_exceeds_balance');
        }
    }
}
