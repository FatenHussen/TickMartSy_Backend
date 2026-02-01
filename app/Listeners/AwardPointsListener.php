<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Support\Facades\Log;

class AwardPointsListener
{
    protected PointService $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
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
        $this->pointService->awardPoints(
            userId: $user->id,
            ruleCode: 'user_registration',
            referenceType: 'user',
            referenceId: $user->id
        );

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
     * Handle order completion points
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
        
        // Award review points
        $this->pointService->awardPoints(
            userId: $review->user_id,
            ruleCode: 'product_review',
            referenceType: 'review',
            referenceId: $review->id
        );

        Log::info("Review points awarded to user {$review->user_id}");
    }
}