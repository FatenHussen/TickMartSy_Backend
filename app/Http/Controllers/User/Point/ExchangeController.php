<?php

namespace App\Http\Controllers\User\Point;

use App\Http\Controllers\Controller;
use App\Services\PointExchangeService;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function __construct(
        private PointExchangeService $exchangeService
    ) {}

    /**
     * Get available exchange options
     */
    public function options()
    {
        $userId = auth('user')->id();
        $options = $this->exchangeService->getExchangeOptions($userId);

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Exchange points for coupon
     */
    public function exchangeForCoupon(Request $request)
    {
        $request->validate([
            'points' => 'required|integer|min:100|max:10000',
            'coupon_id' => 'nullable|integer|exists:coupons,id',
        ]);

        $userId = auth('user')->id();

        $result = $this->exchangeService->exchangeForCoupon(
            $userId,
            $request->points,
            $request->coupon_id
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في استبدال النقاط. تأكد من رصيدك.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استبدال النقاط بكوبون خصم بنجاح',
            // 'data' => [
            //     'exchange_id' => $result['exchange']->id,
            //     'discount_amount' => $result['discount_amount'],
            //     'expires_at' => $result['exchange']->exchange_data['expires_at'],
            // ],
        ]);
    }

    /**
     * Exchange points for free delivery
     */
    public function exchangeForFreeDelivery(Request $request)
    {
        $request->validate([
            'delivery_zones' => 'nullable|array',
            'delivery_zones.*' => 'string',
        ]);

        $userId = auth('user')->id();

        $result = $this->exchangeService->exchangeForFreeDelivery(
            $userId,
            $request->delivery_zones
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في استبدال النقاط. تأكد من رصيدك.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استبدال النقاط بتوصيل مجاني بنجاح',
        ]);
    }

    /**
     * Exchange points for gift
     */
    public function exchangeForGift(Request $request)
    {
        $request->validate([
            'gift_id' => 'required|integer|exists:gifts,id',
            'delivery_address' => 'nullable|array',
            'delivery_address.name' => 'required_with:delivery_address|string',
            'delivery_address.phone' => 'required_with:delivery_address|string',
            'delivery_address.address' => 'required_with:delivery_address|string',
            'delivery_address.city' => 'required_with:delivery_address|string',
        ]);

        $userId = auth('user')->id();

        $result = $this->exchangeService->exchangeForGift(
            $userId,
            $request->gift_id,
            $request->delivery_address
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في استبدال النقاط. تأكد من توفر الهدية ورصيدك.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم طلب الهدية بنجاح. سيتم التواصل معك لترتيب التسليم.',

        ]);
    }

    /**
     * Get user exchange history
     */
    public function history(Request $request)
    {
        $userId = auth('user')->id();
        $perPage = $request->get('per_page', 15);

        $history = $this->exchangeService->getUserExchangeHistory($userId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get user active exchanges (currently usable)
     */
    public function activeExchanges()
    {
        $userId = auth('user')->id();

        $activeExchanges = $this->exchangeService->getUserActiveExchanges($userId);

        return response()->json([
            'success' => true,
            'data' => $activeExchanges,
        ]);
    }

    /**
     * Check active free delivery status
     */
    public function freeDeliveryStatus()
    {
        $userId = auth('user')->id();
        $hasActive = $this->exchangeService->hasActiveFreeDelivery($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_free_delivery' => $hasActive,
                'message' => $hasActive ? 'لديك توصيل مجاني نشط' : 'لا يوجد توصيل مجاني نشط',
            ],
        ]);
    }
}
