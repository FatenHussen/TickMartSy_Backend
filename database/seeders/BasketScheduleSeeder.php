<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\BasketSchedule;
use Illuminate\Database\Seeder;

class BasketScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $baskets = Basket::where('is_schedule', 1)->get();

        if ($baskets->isEmpty()) {
            $this->command->warn('No baskets found, skipping BasketScheduleSeeder.');
            return;
        }

        foreach ($baskets as $basket) {
            $schedules = [
                [
                    'number_of_days' => 3,
                    'title' => [
                        'en' => 'Every 3 Days',
                        'ar' => 'كل 3 أيام',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 5,
                    'is_default' => 1
                ],
                [
                    'number_of_days' => 7,

                    'type' => 'weekly',
                    'title' => [
                        'en' => 'Weekly',
                        'ar' => 'أسبوعي',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'is_default' => 0
                ],
                [
                    'number_of_days' => 14,
                    'title' => [
                        'en' => 'Every Two Weeks',
                        'ar' => 'كل أسبوعين',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 15,
                    'is_default' => 0
                ],
                [
                    'number_of_days' => 30,
                    'title' => [
                        'en' => 'Monthly',
                        'ar' => 'شهري',
                    ],
                    'discount_type' => 'percentage',
                    'discount_value' => 20,
                    'is_default' => 0
                ],
            ];

            foreach ($schedules as $schedule) {
                BasketSchedule::updateOrCreate(
                    [
                        'basket_id' => $basket->id,
                        'number_of_days' => $schedule['number_of_days'],
                    ],
                    [
                        'title'          => $schedule['title'],
                        'discount_type'  => $schedule['discount_type'],
                        'discount_value' => $schedule['discount_value'],
                        'is_active'      => true,
                        'is_default' => $schedule['is_default']
                    ]
                );
            }
        }

        $this->command->info('Basket schedules seeded successfully.');
    }
}
