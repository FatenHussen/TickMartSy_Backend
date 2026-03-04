<?php

namespace App\Services\Admin;

use App\Enums\PromotionStatus;
use App\Models\PromotionRequest;
use App\Services\BaseService;
use App\Http\Resources\Admin\PromotionRequest\AllResource;
use App\Http\Resources\Admin\PromotionRequest\OneResource;
use App\Helpers\SendFCMNotification;
use Illuminate\Support\Facades\DB;

class PromotionRequestService extends BaseService
{
    protected $model = PromotionRequest::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by vendor
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Filter by shop
        if (!empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query;
    }

    /**
     * قبول طلب الترويج
     */
    public function approve(int $id, array $data = []): PromotionRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $request = PromotionRequest::with(['vendor', 'shop'])->findOrFail($id);

            if ($request->status !== PromotionStatus::PENDING) {
                throw new \Exception('يمكن قبول الطلبات المعلقة فقط');
            }

            $request->update([
                'status' => PromotionStatus::APPROVED,
                'admin_notes' => $data['admin_notes'] ?? null,
                'approved_at' => now(),
                'approved_by' => auth('admin')->id(),
            ]);

            // إرسال إشعار للفيندور
            $this->sendNotificationToVendor($request, 'approved');

            return $request->fresh(['vendor', 'shop', 'approvedBy']);
        });
    }

    /**
     * رفض طلب الترويج
     */
    public function reject(int $id, array $data): PromotionRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $request = PromotionRequest::with(['vendor', 'shop'])->findOrFail($id);

            if ($request->status !== PromotionStatus::PENDING) {
                throw new \Exception('يمكن رفض الطلبات المعلقة فقط');
            }

            if (empty($data['admin_notes'])) {
                throw new \Exception('يجب إدخال سبب الرفض');
            }

            $request->update([
                'status' => PromotionStatus::REJECTED,
                'admin_notes' => $data['admin_notes'],
                'approved_at' => now(),
                'approved_by' => auth('admin')->id(),
            ]);

            // إرسال إشعار للفيندور
            $this->sendNotificationToVendor($request, 'rejected');

            return $request->fresh(['vendor', 'shop', 'approvedBy']);
        });
    }

    /**
     * إرسال إشعار للفيندور
     */
    protected function sendNotificationToVendor(PromotionRequest $request, string $action): void
    {
        try {
            $vendor = $request->vendor;
            if (!$vendor) return;

            // جلب جميع مستخدمي الفيندور
            $vendorUsers = $vendor->users;
            if ($vendorUsers->isEmpty()) return;

            $title = $action === 'approved'
                ? 'تم قبول طلب الترويج'
                : 'تم رفض طلب الترويج';

            $body = $action === 'approved'
                ? "تم قبول طلب الترويج: {$request->title}"
                : "تم رفض طلب الترويج: {$request->title}. السبب: {$request->admin_notes}";

            foreach ($vendorUsers as $user) {
                // إنشاء إشعار في قاعدة البيانات
                \App\Models\VendorNotification::create([
                    'vendor_user_id' => $user->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'promotion_request',
                    'data' => [
                        'promotion_request_id' => $request->id,
                        'status' => $request->status->value,
                        'action' => $action,
                    ],
                ]);

                // إرسال FCM notification
                $tokens = $user->tokens()->pluck('fcm_token')->filter()->toArray();
                if (!empty($tokens)) {
                    $fcmNotification = new SendFCMNotification(
                        $tokens,
                        $title,
                        $body,
                        [
                            'type' => 'promotion_request',
                            'promotion_request_id' => $request->id,
                            'status' => $request->status->value,
                        ]
                    );
                    $fcmNotification->sendNotification();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send promotion request notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    /**
     * إحصائيات طلبات الترويج
     */
    public function getStats(): array
    {
        return [
            'total' => PromotionRequest::count(),
            'pending' => PromotionRequest::where('status', PromotionStatus::PENDING)->count(),
            'approved' => PromotionRequest::where('status', PromotionStatus::APPROVED)->count(),
            'rejected' => PromotionRequest::where('status', PromotionStatus::REJECTED)->count(),
            'active' => PromotionRequest::active()->count(),
        ];
    }
}
