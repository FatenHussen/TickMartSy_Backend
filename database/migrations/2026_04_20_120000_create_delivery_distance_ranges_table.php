<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_distance_ranges', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_distance', 8, 2);
            $table->decimal('max_distance', 8, 2)->nullable();
            $table->decimal('multiplier', 8, 2);
            $table->timestamps();

            $table->index('min_distance');
        });

        DB::table('delivery_distance_ranges')->insert([
            [
                'min_distance' => 0,
                'max_distance' => 5,
                'multiplier' => 1.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'min_distance' => 5,
                'max_distance' => 8,
                'multiplier' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'min_distance' => 8,
                'max_distance' => null,
                'multiplier' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_distance_ranges');
    }
};
