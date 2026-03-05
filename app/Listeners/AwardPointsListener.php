<?php

namespace App\Listeners;

use App\Services\PointService;
use App\Services\Base\NotificationService;
use App\Events\OrderStatusChanged;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class AwardPointsListener
{
    protected PointService $pointService;
    protected NotificationService $notificationService;

    public function __construct(
        PointService $pointService,
        NotificationService $notificationService
    ) {
        $this->pointService = $pointService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle user registration points
     */
    public function handleUserRegistered($event): void
    {
        $user = $event->user;

        // Check if already awarded
        if ($this->pointService->isEventCompleted($user->id, 'user_registered')) {
            return;
        }

        // Award registration points
        $transaction = $this->pointService->awardPoints(
            userId: $user->id,
            ruleCode: 'user_registration',
            referenceType: 'user',
            referenceId: $user->id
        );

        if ($transaction) {
            // Send notification
            $this->notificationService->send(
                recipient: $user,
                title: '🎉 مرحباً بك!',
                body: "تهانينا! حصلت على {$transaction->points} نقطة كمكافأة تسجيل",
                data: [
                    'type' => 'points_earned',
                    'points' => $transaction->points,
                    'reason' => 'user_registration'
                ]
            );
        }

        // Mark event as completed
        $this->pointService->markEventCompleted($user->id, 'user_registered');

        Log::info("Registration points awarded to user {$user->id}");
    }

    /**
     * Handle first order points
     */
    public function handleFirstOrder($event): void
    {
        $order = $event->order;
        $userId = $order->user_id;

        // Check if this is truly the first order
        if ($this->pointService->isEventCompleted($userId, 'first_order')) {
            return;
        }

        // Award first order points
        $this->pointService->awardPoints(
            userId: $userId,
            ruleCode: 'first_order',
            orderAmount: $order->total,
            referenceType: 'order',
            referenceId: $order->id,
            status: 'pending' // Will be confirmed when order is completed
        );

        // Mark event as completed
        $this->pointService->markEventCompleted($userId, 'first_order');

        Log::info("First order points awarded to user {$userId}");
    }

    /**
     * Handle order status changes - award points when order is delivered
     */
    public function handleOrderStatusChanged(OrderStatusChanged $event): void
    {
        // Only award points when order becomes delivered
        if ($event->to !== OrderStatus::DELIVERED->value) {
            return;
        }

        // Prevent duplicate awards if status was already delivered
        if ($event->from === OrderStatus::DELIVERED->value) {
            return;
        }

        $order = $event->order;
        $userId = $order->user_id;
        $user = $order->user;

        try {
            // Check if points already awarded for this order to prevent duplicates
            $existingTransaction = \App\Models\PointTransaction::where('reference_type', 'order')
                ->where('reference_id', $order->id)
                ->where('source', 'order_completion')
                ->exists();

            if ($existingTransaction) {
                Log::info("Points already awarded for order {$order->id}, skipping");
                return;
            }

            $totalPointsEarned = 0;
            $pointsBreakdown = [];

            // Award first order points if this is the first order
            if (!$this->pointService->isEventCompleted($userId, 'first_order')) {
                $transaction = $this->pointService->awardPoints(
                    userId: $userId,
                    ruleCode: 'first_order',
                    orderAmount: $order->total,
                    referenceType: 'order',
                    referenceId: $order->id
                );

                if ($transaction) {
                    $totalPointsEarned += $transaction->points;
                    $pointsBreakdown[] = "🎁 مكافأة أول طلب: {$transaction->points} نقطة";
                }

                $this->pointService->markEventCompleted($userId, 'first_order');
                Log::info("First order points awarded to user {$userId} for order {$order->id}");
            }

            // Award order completion points (for every completed order)
            $transaction = $this->pointService->awardPoints(
                userId: $userId,
                ruleCode: 'order_completion',
                orderAmount: $order->total,
                referenceType: 'order',
                referenceId: $order->id
            );

            if ($transaction) {
                $totalPointsEarned += $transaction->points;
                $pointsBreakdown[] = "✅ إتمام الطلب: {$transaction->points} نقطة";
            }

            Log::info("Order completion points awarded to user {$userId} for order {$order->id}");

            // Award purchase amount threshold bonus if applicable
            $transaction = $this->pointService->awardPoints(
                userId: $userId,
                ruleCode: 'purchase_amount_threshold',
                orderAmount: $order->total,
                referenceType: 'order',
                referenceId: $order->id
            );

            if ($transaction) {
                $totalPointsEarned += $transaction->points;
                $pointsBreakdown[] = "💰 مكافأة قيمة الشراء: {$transaction->points} نقطة";
            }

            // Send notification with all points earned
            if ($totalPointsEarned > 0 && $user) {
                $breakdownText = implode("\n", $pointsBreakdown);

                $this->notificationService->send(
                    recipient: $user,
                    title: '🎉 حصلت على نقاط!',
                    body: "تم توصيل طلبك بنجاح! حصلت على {$totalPointsEarned} نقطة\n\n{$breakdownText}",
                    data: [
                        'type' => 'points_earned',
                        'points' => $totalPointsEarned,
                        'order_id' => $order->id,
                        'breakdown' => $pointsBreakdown
                    ]
                );
            }

            Log::info("Points awarded to user {$userId} for order {$order->id}");
        } catch (\Throwable $e) {
            // Don't fail order completion if points fail
            Log::error("Failed to award points for order {$order->id}", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
        }
    }

    /**
     * Handle order completion points (legacy method - kept for backward compatibility)
     */
    public function handleOrderCompleted($event): void
    {
        $order = $event->order;

        // Confirm any pending points for this order
        $this->pointService->confirmPendingPoints('order', $order->id);

        // Award completion points
        $this->pointService->awardPoints(
            userId: $order->user_id,
            ruleCode: 'order_completion',
            orderAmount: $order->total,
            referenceType: 'order',
            referenceId: $order->id
        );

        Log::info("Order completion points awarded for order {$order->id}");
    }

    /**
     * Handle review points
     */
    public function handleReviewSubmitted($event): void
    {
        $review = $event->review;
        $user = $review->user;

        // Award review points
        $transaction = $this->pointService->awardPoints(
            userId: $review->user_id,
            ruleCode: 'product_review',
            referenceType: 'review',
            referenceId: $review->id
        );

        if ($transaction && $user) {
            // Send notification
            $this->notificationService->send(
                recipient: $user,
                title: '⭐ شكراً على تقييمك!',
                body: "حصلت على {$transaction->points} نقطة مقابل تقييم المنتج",
                data: [
                    'type' => 'points_earned',
                    'points' => $transaction->points,
                    'reason' => 'product_review',
                    'review_id' => $review->id
                ]
            );
        }

        Log::info("Review points awarded to user {$review->user_id}");
    }
}
