<?php

namespace App\Http\Controllers\User\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * الحصول على مزايا الباقة المتاحة لليوزر
     *
     * @return JsonResponse
     */
    public function benefits(): JsonResponse
    {
        $user = auth('user')->user();

        if (!$user) {
            return $this->sendError('المستخدم غير مسجل دخول', [], 401);
        }

        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$subscription || !$subscription->isActive()) {
            return $this->sendResponse([
                'has_subscription' => false,
                'remaining_discounts' => 0,
                'remaining_free_deliveries' => 0,
            ], 'لا يوجد اشتراك نشط');
        }

        return $this->sendResponse([
            'has_subscription' => true,
            'remaining_discounts' => $subscription->remaining_orders ?? 0,
            'remaining_free_deliveries' => $subscription->remaining_free_deliveries ?? 0,
        ], 'تم جلب مزايا الباقة بنجاح');
    }
}
