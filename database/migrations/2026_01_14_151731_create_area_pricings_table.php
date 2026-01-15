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
        Schema::create('area_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_fee', 8, 2);

            $table->decimal('distance_0_5_multiplier', 4, 2)->default(1.50);
            $table->decimal('distance_5_8_multiplier', 4, 2)->default(2.00);
            $table->decimal('distance_8_plus_multiplier', 4, 2)->default(3.00);

            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_pricings');
    }
};
