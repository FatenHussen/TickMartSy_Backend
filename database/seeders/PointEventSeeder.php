<?php

namespace Database\Seeders;

use App\Models\PointEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $events = [];

        foreach ($users as $user) {
            $events[] = [
                'user_id' => $user->id,
                'event_key' => 'user_registered',
                'created_at' => $user->created_at,
                'updated_at' => $user->created_at,
            ];

            if (rand(1, 10) <= 8) {
                $events[] = [
                    'user_id' => $user->id,
                    'event_key' => 'first_order',
                    'created_at' => $user->created_at->addDays(rand(1, 7)),
                    'updated_at' => $user->created_at->addDays(rand(1, 7)),
                ];
            }

            if (rand(1, 10) <= 3) {
                for ($day = 1; $day <= rand(3, 10); $day++) {
                    $events[] = [
                        'user_id' => $user->id,
                        'event_key' => 'daily_login_' . now()->subDays($day)->format('Y-m-d'),
                        'created_at' => now()->subDays($day),
                        'updated_at' => now()->subDays($day),
                    ];
                }
            }

            if (rand(1, 10) <= 2) {
                $specialEvents = [
                    'birthday_bonus_2024',
                    'new_year_bonus_2024',
                    'ramadan_bonus_2024',
                    'app_anniversary_bonus',
                ];

                $eventKey = $specialEvents[array_rand($specialEvents)];
                $events[] = [
                    'user_id' => $user->id,
                    'event_key' => $eventKey,
                    'created_at' => now()->subDays(rand(10, 60)),
                    'updated_at' => now()->subDays(rand(10, 60)),
                ];
            }
        }

        $chunks = array_chunk($events, 500);
        foreach ($chunks as $chunk) {
            PointEvent::insert($chunk);
        }

        $this->command->info('Created ' . count($events) . ' point events for ' . $users->count() . ' users.');
    }
}