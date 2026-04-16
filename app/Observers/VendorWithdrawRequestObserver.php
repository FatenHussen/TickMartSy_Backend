<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\VendorWithdrawRequest;
use App\Services\Base\NotificationService;
use App\Services\Vendor\VendorNotificationService;

class VendorWithdrawRequestObserver
{
    public function created(VendorWithdrawRequest $request): void
    {
        if ($request->status !== 'pending') {
            return;
        }

        $title = __('custom.withdrawals.admin_new_request_title');
        $body = __('custom.withdrawals.admin_new_request_body', [
            'id' => $request->id,
            'amount' => number_format((float) $request->amount, 2),
            'vendor_id' => $request->vendor_id,
        ]);

        $notificationService = app(NotificationService::class);

        Admin::query()->chunk(100, function ($admins) use ($notificationService, $title, $body, $request): void {
            foreach ($admins as $admin) {
                $notificationService->send(
                    $admin,
                    $title,
                    $body,
                    [
                        'type' => 'vendor_withdraw_request_created',
                        'withdraw_request_id' => (string) $request->id,
                        'vendor_id' => (string) $request->vendor_id,
                        'status' => (string) $request->status,
                        'amount' => (string) round((float) $request->amount, 2),
                    ]
                );
            }
        });
    }

    public function updated(VendorWithdrawRequest $request): void
    {
        if (! $request->wasChanged('status')) {
            return;
        }

        $status = (string) $request->status;
        if (! in_array($status, ['paid', 'rejected'], true)) {
            return;
        }

        $notificationService = app(VendorNotificationService::class);

        if ($status === 'paid') {
            $notificationService->notifyVendor(
                $request->vendor_id,
                __('custom.withdrawals.vendor_status_paid_title'),
                __('custom.withdrawals.vendor_status_paid_body', [
                    'id' => $request->id,
                    'amount' => number_format((float) $request->amount, 2),
                ]),
                'vendor_withdraw_request_paid',
                [
                    'withdraw_request_id' => (string) $request->id,
                    'status' => 'paid',
                    'amount' => (string) round((float) $request->amount, 2),
                ]
            );

            return;
        }

        $notificationService->notifyVendor(
            $request->vendor_id,
            __('custom.withdrawals.vendor_status_rejected_title'),
            __('custom.withdrawals.vendor_status_rejected_body', [
                'id' => $request->id,
                'reason' => $request->rejection_reason ?: '-',
            ]),
            'vendor_withdraw_request_rejected',
            [
                'withdraw_request_id' => (string) $request->id,
                'status' => 'rejected',
                'rejection_reason' => (string) ($request->rejection_reason ?: ''),
            ]
        );
    }
}