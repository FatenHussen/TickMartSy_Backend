<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\UserGift\AllResource;
use App\Http\Resources\Admin\UserGift\OneResource;
use App\Models\UserGift;
use App\Services\BaseService;
use App\Jobs\SendFcmNotificationJob;

class UserGiftService extends BaseService
{
    protected $model = UserGift::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['gift', 'user', 'address'];

    protected $searchableFields = ['id'];

    protected $sortableFields = ['id', 'created_at', 'status'];

    protected $pagination = true;

    /**
     * Override create to send notification
     */
    public function create($data)
    {
        $result = parent::create($data);

        // Send notification to user
        if ($result->resource) {
            $userGift = $this->model::with('gift', 'user')->find($result->resource->id);
            if ($userGift && $userGift->user) {
                $this->sendGiftNotification($userGift, 'new_gift');
            }
        }

        return $result;
    }

    /**
     * Override update to send notification on status change
     */
    public function update($id, array $data)
    {
        $userGift = $this->model::find($id);
        $oldStatus = $userGift->status;

        $result = parent::update($id, $data);

        // Send notification if status changed
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $userGift = $this->model::with('gift', 'user')->find($id);
            if ($userGift && $userGift->user) {
                $this->sendGiftNotification($userGift, 'status_changed');
            }
        }

        return $result;
    }

    /**
     * Send notification to user about gift
     */
    protected function sendGiftNotification($userGift, $type)
    {
        $locale = app()->getLocale();

        $titles = [
            'new_gift' => [
                'ar' => 'هدية جديدة!',
                'en' => 'New Gift!',
            ],
            'status_changed' => [
                'ar' => 'تحديث حالة الهدية',
                'en' => 'Gift Status Update',
            ],
        ];

        $bodies = [
            'new_gift' => [
                'ar' => 'لقد حصلت على هدية جديدة! يرجى تحديد عنوان التوصيل.',
                'en' => 'You have received a new gift! Please set your delivery address.',
            ],
            'status_changed' => [
                'ar' => "حالة هديتك الآن: {$this->getStatusLabel($userGift->status, 'ar')}",
                'en' => "Your gift status is now: {$this->getStatusLabel($userGift->status, 'en')}",
            ],
        ];

        $title = $titles[$type][$locale] ?? $titles[$type]['ar'];
        $body = $bodies[$type][$locale] ?? $bodies[$type]['ar'];

        // Get all FCM tokens for the user
        $fcmTokens = $userGift->user->fcmTokens()->pluck('fcm_token')->toArray();

        if (!empty($fcmTokens)) {
            SendFcmNotificationJob::dispatch(
                $fcmTokens,
                $title,
                $body,
                [
                    'type' => 'user_gift',
                    'user_gift_id' => (string) $userGift->id,
                    'gift_id' => (string) $userGift->gift_id,
                    'status' => $userGift->status,
                ]
            );
        }
    }

    /**
     * Get status label in specific language
     */
    protected function getStatusLabel($status, $locale = 'ar')
    {
        $labels = [
            'pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            'processing' => ['ar' => 'قيد المعالجة', 'en' => 'Processing'],
            'shipped' => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
            'delivered' => ['ar' => 'تم التسليم', 'en' => 'Delivered'],
            'cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$locale] ?? $status;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
            unset($filters['user_id']);
        }

        // Filter by gift_id
        if (!empty($filters['gift_id'])) {
            $query->where('gift_id', $filters['gift_id']);
            unset($filters['gift_id']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
