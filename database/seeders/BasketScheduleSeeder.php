<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Support\ScheduleDiscount;
use Illuminate\Database\Seeder;

class BasketScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $baskets = Basket::where('is_schedule', 1)->with('catalogSchedule')->get();

        if ($baskets->isEmpty()) {
            $this->command->warn('No baskets found, skipping BasketScheduleSeeder.');
            return;
        }

        foreach ($baskets as $basket) {
            $schedule = $basket->catalogSchedule;

            if (!$schedule) {
                continue;
            }

            $basket->schedules()->delete();
            $basket->schedules()->create([
                'title' => $schedule->getTranslations('name'),
                'number_of_days' => (int) $schedule->interval_days,
                'discount_type' => ScheduleDiscount::normalizeType($basket->resolvedDiscountType()),
                'discount_value' => $basket->resolvedDiscountValue(),
                'is_active' => true,
                'is_default' => true,
            ]);
        }

        $this->command->info('Basket schedules seeded successfully.');
    }
}
