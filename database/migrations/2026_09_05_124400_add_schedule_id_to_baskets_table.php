<?php

use App\Models\Basket;
use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            $table->foreignId('schedule_id')
                ->nullable()
                ->after('is_schedule')
                ->constrained('schedules')
                ->nullOnDelete();
            $table->boolean('has_custom_discount')
                ->default(false)
                ->after('discount_type');
        });

        $this->backfillScheduledBaskets();
    }

    public function down(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
            $table->dropColumn('has_custom_discount');
        });
    }

    private function backfillScheduledBaskets(): void
    {
        Basket::query()
            ->where('is_schedule', true)
            ->with(['schedules' => fn ($q) => $q->orderByDesc('is_default')])
            ->chunkById(100, function ($baskets) {
                foreach ($baskets as $basket) {
                    $days = (int) ($basket->schedules->first()?->number_of_days ?? 0);
                    $schedule = $this->resolveCatalogSchedule($days);

                    if (!$schedule) {
                        continue;
                    }

                    $snapshot = $basket->schedules->firstWhere('is_default', true) ?? $basket->schedules->first();
                    $catalogDiscount = (float) ($schedule->discount_value ?? 0);
                    $snapshotDiscount = (float) ($snapshot?->discount_value ?? $basket->discount ?? 0);
                    $hasCustom = $snapshotDiscount > 0 && abs($snapshotDiscount - $catalogDiscount) > 0.009;

                    $basket->forceFill([
                        'schedule_id' => $schedule->id,
                        'has_custom_discount' => $hasCustom,
                    ])->saveQuietly();
                }
            });
    }

    private function resolveCatalogSchedule(int $days): ?Schedule
    {
        if ($days > 0) {
            $existing = Schedule::query()->where('interval_days', $days)->first();
            if ($existing) {
                return $existing;
            }

            return Schedule::create([
                'name' => [
                    'ar' => "كل {$days} أيام",
                    'en' => "Every {$days} days",
                ],
                'interval_days' => $days,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'is_active' => true,
            ]);
        }

        return Schedule::query()->orderBy('id')->first();
    }
};
