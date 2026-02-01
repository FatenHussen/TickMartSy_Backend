<?php

namespace Database\Seeders;

use App\Models\PointWallet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PointWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create wallet with random balance and dates
            $balance = rand(0, 500);
            $lastEarnedAt = $balance > 0 ? Carbon::now()->subDays(rand(1, 30)) : null;
            $expireAt = $lastEarnedAt ? $lastEarnedAt->copy()->addYear() : null;

            PointWallet::create([
                'user_id' => $user->id,
                'balance' => $balance,
                'expire_at' => $expireAt,
                'last_earned_at' => $lastEarnedAt,
            ]);
        }

        $this->command->info('Created point wallets for ' . $users->count() . ' users.');
    }
}