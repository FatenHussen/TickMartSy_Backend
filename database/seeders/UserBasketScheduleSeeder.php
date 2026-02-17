<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Schedule;
use App\Models\UserBasketSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UserBasketScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        // $category = Category::first();
        $schedule = Schedule::first();

        if (!$user || !$schedule) {
            return;
        }

        UserBasketSchedule::create([
            'user_id' => $user->id,
            // 'category_id' => $category->id,
            'schedule_id' => $schedule->id,
            'name' => 'سلة الفطور',
            'is_active' => true,
            'start_date' => Carbon::now(),
            'next_run_date' => Carbon::now()->addDays($schedule->interval_days),
        ]);
    }
}
