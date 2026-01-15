<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\BasketSchedule;
use Illuminate\Database\Seeder;

class BasketScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $baskets = Basket::all();

        if ($baskets->isEmpty()) {
            $this->command->warn('No baskets found, skipping BasketScheduleSeeder.');
            return;
        }

        foreach ($baskets as $basket) {
            $schedules = [
                [
                    'type' => '3_days',
                    'title' => [
                        'en' => 'Every 3 Days',
                        'ar' => 'كل 3 أيام',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 5,
                ],
                [
                    'type' => 'weekly',
                    'title' => [
                        'en' => 'Weekly',
                        'ar' => 'أسبوعي',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                ],
                [
                    'type' => 'biweekly',
                    'title' => [
                        'en' => 'Every Two Weeks',
                        'ar' => 'كل أسبوعين',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 15,
                ],
                [
                    'type' => 'monthly',
                    'title' => [
                        'en' => 'Monthly',
                        'ar' => 'شهري',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 20,
                ],
            ];

            foreach ($schedules as $schedule) {
                BasketSchedule::updateOrCreate(
                    [
                        'basket_id' => $basket->id,
                        'type' => $schedule['type'],
                    ],
                    [
                        'title'          => $schedule['title'],
                        'discount_type'  => $schedule['discount_type'],
                        'discount_value' => $schedule['discount_value'],
                        'is_active'      => true,
                    ]
                );
            }
        }

        $this->command->info('Basket schedules seeded successfully.');
    }
}
