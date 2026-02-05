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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->decimal('price', 10, 2);
            $table->integer('duration_days'); // 30, 90 ...

            // Features
            $table->integer('monthly_orders_limit')->nullable();
            $table->integer('free_delivery_count')->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->integer('points_bonus')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
