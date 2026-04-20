<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('delivery_distance_ranges')) {
            return;
        }

        $hasIsActive = Schema::hasColumn('delivery_distance_ranges', 'is_active');
        $hasSortOrder = Schema::hasColumn('delivery_distance_ranges', 'sort_order');

        Schema::table('delivery_distance_ranges', function (Blueprint $table) use ($hasIsActive, $hasSortOrder): void {
            if ($hasIsActive) {
                $table->dropColumn('is_active');
            }

            if ($hasSortOrder) {
                $table->dropColumn('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('delivery_distance_ranges')) {
            return;
        }

        $hasIsActive = Schema::hasColumn('delivery_distance_ranges', 'is_active');
        $hasSortOrder = Schema::hasColumn('delivery_distance_ranges', 'sort_order');

        Schema::table('delivery_distance_ranges', function (Blueprint $table) use ($hasIsActive, $hasSortOrder): void {
            if (!$hasIsActive) {
                $table->boolean('is_active')->default(true);
            }

            if (!$hasSortOrder) {
                $table->unsignedInteger('sort_order')->default(0);
            }
        });
    }
};
