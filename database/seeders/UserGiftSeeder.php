<?php

namespace Database\Seeders;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserGift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserGiftSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users and gifts
        $users = User::limit(5)->get();
        $gifts = Gift::limit(7)->get();

        if ($users->isEmpty() || $gifts->isEmpty()) {
            $this->command->warn('No users or gifts found. Please run UserSeeder and GiftSeeder first.');
            return;
        }

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $userGifts = [];

        // Create 20 user gifts with different statuses
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $gift = $gifts->random();
            $status = $statuses[array_rand($statuses)];

            // Get user address if exists
            $address = UserAddress::where('user_id', $user->id)->first();

            $userGift = [
                'gift_id' => $gift->id,
                'user_id' => $user->id,
                'address_id' => $address?->id,
                'status' => $status,
                'admin_notes' => $status === 'cancelled'
                    ? 'Cancelled due to out of stock'
                    : ($status === 'delivered' ? 'Delivered successfully' : null),
                'user_notes' => $i % 3 === 0 ? 'Please deliver during business hours' : null,
                'delivered_at' => $status === 'delivered'
                    ? Carbon::now()->subDays(rand(1, 30))
                    : null,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ];

            $userGifts[] = $userGift;
        }

        // Insert all user gifts
        foreach ($userGifts as $userGift) {
            UserGift::create($userGift);
        }

        $this->command->info('Created ' . count($userGifts) . ' user gifts.');

        // Display statistics
        $this->command->info('Statistics:');
        $this->command->info('- Pending: ' . UserGift::where('status', 'pending')->count());
        $this->command->info('- Processing: ' . UserGift::where('status', 'processing')->count());
        $this->command->info('- Shipped: ' . UserGift::where('status', 'shipped')->count());
        $this->command->info('- Delivered: ' . UserGift::where('status', 'delivered')->count());
        $this->command->info('- Cancelled: ' . UserGift::where('status', 'cancelled')->count());
    }
}
