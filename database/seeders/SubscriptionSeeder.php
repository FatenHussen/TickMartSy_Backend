<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(10)->get();
        $packages = Package::where('is_active', true)->get();

        if ($users->isEmpty() || $packages->isEmpty()) {
            $this->command->warn('No users or packages found. Skipping subscription seeding.');
            return;
        }

        $subscriptions = [];

        // Active Subscriptions (اشتراكات فعالة)
        foreach ($users->take(5) as $index => $user) {
            $package = $packages->random();
            $startDate = now()->subDays(rand(5, 20));
            $endDate = $startDate->copy()->addDays($package->duration_days);

            $subscriptions[] = [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => 'active',
                'remaining_orders' => $package->monthly_orders_limit ? rand(10, $package->monthly_orders_limit) : null,
                'remaining_free_deliveries' => rand(0, $package->free_delivery_count),
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }

        // Expired Subscriptions (اشتراكات منتهية)
        foreach ($users->skip(5)->take(3) as $user) {
            $package = $packages->random();
            $startDate = now()->subDays(rand(60, 90));
            $endDate = $startDate->copy()->addDays($package->duration_days);

            $subscriptions[] = [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => 'expired',
                'remaining_orders' => 0,
                'remaining_free_deliveries' => 0,
                'created_at' => $startDate,
                'updated_at' => $endDate,
            ];
        }

        // Cancelled Subscriptions (اشتراكات ملغية)
        foreach ($users->skip(8)->take(2) as $user) {
            $package = $packages->random();
            $startDate = now()->subDays(rand(10, 30));
            $endDate = $startDate->copy()->addDays($package->duration_days);
            $cancelledDate = $startDate->copy()->addDays(rand(5, 15));

            $subscriptions[] = [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => 'cancelled',
                'remaining_orders' => $package->monthly_orders_limit ? rand(20, $package->monthly_orders_limit) : null,
                'remaining_free_deliveries' => rand(2, $package->free_delivery_count),
                'created_at' => $startDate,
                'updated_at' => $cancelledDate,
            ];
        }

        // Additional Active Subscriptions with different scenarios
        // اشتراك جديد (بدأ اليوم)
        if ($users->count() > 0 && $packages->count() > 0) {
            $package = $packages->random();
            $subscriptions[] = [
                'user_id' => $users->random()->id,
                'package_id' => $package->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays($package->duration_days)->format('Y-m-d'),
                'status' => 'active',
                'remaining_orders' => $package->monthly_orders_limit,
                'remaining_free_deliveries' => $package->free_delivery_count,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // اشتراك قارب على الانتهاء (باقي 3 أيام)
            $package = $packages->random();
            $startDate = now()->subDays($package->duration_days - 3);
            $subscriptions[] = [
                'user_id' => $users->random()->id,
                'package_id' => $package->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => now()->addDays(3)->format('Y-m-d'),
                'status' => 'active',
                'remaining_orders' => $package->monthly_orders_limit ? rand(1, 5) : null,
                'remaining_free_deliveries' => rand(0, 2),
                'created_at' => $startDate,
                'updated_at' => now(),
            ];

            // اشتراك استنفذ كل الطلبات
            $package = $packages->random();
            $startDate = now()->subDays(rand(15, 25));
            $subscriptions[] = [
                'user_id' => $users->random()->id,
                'package_id' => $package->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addDays($package->duration_days)->format('Y-m-d'),
                'status' => 'active',
                'remaining_orders' => 0,
                'remaining_free_deliveries' => 0,
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }

        foreach ($subscriptions as $subscription) {
            Subscription::create($subscription);
        }

        $this->command->info('Subscriptions seeded successfully!');
        $this->command->info('Total subscriptions created: ' . count($subscriptions));
    }
}

