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
        Schema::create('couponables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coupon_id')
                ->constrained()
                ->cascadeOnDelete();

            // polymorphic columns
            $table->morphs('couponable');
            // creates: couponable_id, couponable_type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couponables');
    }
};
