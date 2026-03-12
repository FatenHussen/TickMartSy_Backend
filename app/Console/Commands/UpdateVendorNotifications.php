<?php

namespace App\Console\Commands;

use App\Models\VendorNotification;
use App\Models\VendorUser;
use Illuminate\Console\Command;

class UpdateVendorNotifications extends Command
{
    protected $signature = 'vendor:update-notifications';
    protected $description = 'Update existing vendor notifications with notifiable fields';

    public function handle()
    {
        $this->info('Updating vendor notifications...');

        $updated = VendorNotification::whereNull('notifiable_type')
            ->orWhereNull('notifiable_id')
            ->get()
            ->each(function ($notification) {
                $notification->update([
                    'notifiable_type' => VendorUser::class,
                    'notifiable_id' => $notification->vendor_user_id,
                ]);
            });

        $this->info("Updated {$updated->count()} notifications.");

        return Command::SUCCESS;
    }
}
