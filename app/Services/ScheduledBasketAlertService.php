<?php

namespace App\Services;

use App\Models\Basket;
use App\Models\BasketSchedule;
use App\Models\Order;
use App\Models\ScheduledBasketAlert;
use App\Models\UserBasketSchedule;
use Carbon\Carbon;

class ScheduledBasketAlertService
{
    public function syncForUserSchedule(UserBasketSchedule $basket, array $summary): array
    {
        return $this->syncAlert(
            userId: $basket->user_id,
            basketType: ScheduledBasketAlert::BASKET_TYPE_USER_SCHEDULE,
            basketReferenceId: $basket->id,
            basketScheduleId: null,
            orderId: null,
            nextRunDate: $basket->next_run_date?->format('Y-m-d'),
            payload: $this->buildPayload($basket->name, $summary),
            hasIssue: (bool) $summary['has_issue'],
        );
    }

    public function syncForAdminSchedule(Order $order, Basket $basket, BasketSchedule $basketSchedule, array $summary): array
    {
        return $this->syncAlert(
            userId: $order->user_id,
            basketType: ScheduledBasketAlert::BASKET_TYPE_ADMIN_SCHEDULE,
            basketReferenceId: $basket->id,
            basketScheduleId: $basketSchedule->id,
            orderId: $order->id,
            nextRunDate: $order->next_run_date?->format('Y-m-d'),
            payload: $this->buildPayload($basket->name, $summary),
            hasIssue: (bool) $summary['has_issue'],
        );
    }

    public function decide(int $userId, int $alertId, string $decision): ScheduledBasketAlert
    {
        $alert = ScheduledBasketAlert::query()
            ->where('user_id', $userId)
            ->findOrFail($alertId);

        $alert->update([
            'user_decision' => $decision,
            'decision_at' => now(),
            'status' => $decision === ScheduledBasketAlert::DECISION_DISMISSED
                ? ScheduledBasketAlert::STATUS_DISMISSED
                : $alert->status,
        ]);

        return $alert->fresh();
    }

    public function getOpenAlert(string $basketType, int $userId, int $basketReferenceId): ?ScheduledBasketAlert
    {
        return ScheduledBasketAlert::query()
            ->open()
            ->where('user_id', $userId)
            ->where('basket_type', $basketType)
            ->where('basket_reference_id', $basketReferenceId)
            ->where('alert_type', ScheduledBasketAlert::TYPE_STOCK_ISSUE)
            ->latest('id')
            ->first();
    }

    private function syncAlert(
        int $userId,
        string $basketType,
        int $basketReferenceId,
        ?int $basketScheduleId,
        ?int $orderId,
        ?string $nextRunDate,
        array $payload,
        bool $hasIssue,
    ): array {
        $openAlert = $this->getOpenAlert($basketType, $userId, $basketReferenceId);

        if ($hasIssue) {
            $payloadChanged = $openAlert
                ? md5(json_encode($openAlert->payload ?? [])) !== md5(json_encode($payload))
                : true;

            if (!$openAlert) {
                $openAlert = ScheduledBasketAlert::create([
                    'user_id' => $userId,
                    'basket_type' => $basketType,
                    'basket_reference_id' => $basketReferenceId,
                    'basket_schedule_id' => $basketScheduleId,
                    'order_id' => $orderId,
                    'alert_type' => ScheduledBasketAlert::TYPE_STOCK_ISSUE,
                    'status' => ScheduledBasketAlert::STATUS_OPEN,
                    'payload' => $payload,
                    'next_run_date' => $nextRunDate,
                    'first_detected_at' => now(),
                    'last_detected_at' => now(),
                ]);
            } else {
                $openAlert->update([
                    'basket_schedule_id' => $basketScheduleId,
                    'order_id' => $orderId,
                    'payload' => $payload,
                    'next_run_date' => $nextRunDate,
                    'last_detected_at' => now(),
                ]);
            }

            return [
                'active_alert' => $openAlert->fresh(),
                'resolved_alert' => null,
                'should_notify_issue' => $payloadChanged || !$openAlert->last_notified_at,
                'should_notify_resolved' => false,
            ];
        }

        if (!$openAlert) {
            return [
                'active_alert' => null,
                'resolved_alert' => null,
                'should_notify_issue' => false,
                'should_notify_resolved' => false,
            ];
        }

        $openAlert->update([
            'status' => ScheduledBasketAlert::STATUS_RESOLVED,
            'resolved_at' => now(),
            'last_detected_at' => now(),
        ]);

        return [
            'active_alert' => null,
            'resolved_alert' => $openAlert->fresh(),
            'should_notify_issue' => false,
            'should_notify_resolved' => $openAlert->last_resolution_notified_at === null,
        ];
    }

    public function markIssueNotificationSent(ScheduledBasketAlert $alert): void
    {
        $alert->update([
            'last_notified_at' => now(),
        ]);
    }

    public function markResolutionNotificationSent(ScheduledBasketAlert $alert): void
    {
        $alert->update([
            'last_resolution_notified_at' => now(),
        ]);
    }

    private function buildPayload(?string $basketName, array $summary): array
    {
        return [
            'basket_name' => $basketName,
            'summary_status' => $summary['status'],
            'unavailable_items_count' => $summary['unavailable_items_count'],
            'warning_items_count' => $summary['warning_items_count'],
            'next_run_date' => $summary['next_run_date'],
            'issue_items' => $summary['issue_items'],
        ];
    }
}
