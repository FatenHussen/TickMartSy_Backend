<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            [
                'name' => ['ar' => 'كل 3 أيام', 'en' => 'Every 3 Days'],
                'interval_days' => 3,
                'discount_type' => 'percentage',
                'discount_value' => 5,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'أسبوعي', 'en' => 'Weekly'],
                'interval_days' => 7,
                'discount_type' => 'percentage',
                'discount_value' => 7,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'كل أسبوعين', 'en' => 'Biweekly'],
                'interval_days' => 14,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'شهري', 'en' => 'Monthly'],
                'interval_days' => 30,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
