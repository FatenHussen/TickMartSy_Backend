<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Database\Seeder;

class UserTokenSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users
        $users = User::limit(8)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $tokens = [];

        // Create FCM tokens for users
        foreach ($users as $index => $user) {
            // Some users have multiple devices
            $deviceCount = $index % 3 === 0 ? 2 : 1;

            for ($i = 0; $i < $deviceCount; $i++) {
                $tokens[] = [
                    'device_id' => $this->generateDeviceId($user->id, $i),
                    'fcm_token' => $this->generateFcmToken($user->id, $i),
                    'tokenable_type' => User::class,
                    'tokenable_id' => $user->id,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(0, 15)),
                ];
            }
        }

        // Insert all tokens
        foreach ($tokens as $token) {
            UserToken::create($token);
        }

        $this->command->info('Created ' . count($tokens) . ' user tokens.');
        $this->command->info('- Users with tokens: ' . $users->count());
        $this->command->info('- Users with multiple devices: ' . $users->filter(fn($u, $i) => $i % 3 === 0)->count());
    }

    /**
     * Generate a realistic device ID
     */
    private function generateDeviceId($userId, $deviceIndex): string
    {
        $deviceTypes = [
            'iPhone-14-Pro',
            'Samsung-Galaxy-S23',
            'Pixel-7-Pro',
            'OnePlus-11',
            'Xiaomi-13-Pro',
            'Huawei-P60',
        ];

        $deviceType = $deviceTypes[array_rand($deviceTypes)];
        $uniqueId = strtoupper(substr(md5($userId . $deviceIndex . time()), 0, 16));

        return "{$deviceType}-{$uniqueId}";
    }

    /**
     * Generate a realistic FCM token
     */
    private function generateFcmToken($userId, $deviceIndex): string
    {
        // FCM tokens are typically 152-163 characters long
        $prefix = 'f' . strtoupper(substr(md5($userId . $deviceIndex), 0, 10));
        $middle = ':APA91b' . strtoupper(substr(md5(time() . $userId), 0, 20));
        $suffix = strtoupper(substr(md5($deviceIndex . microtime()), 0, 100));

        return $prefix . $middle . $suffix;
    }
}
