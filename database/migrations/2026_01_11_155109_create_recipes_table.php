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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->decimal('discount', 5, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage');
            $table->unsignedInteger('delivery_price')->default(0);
            $table->float('rating')->default(0);
            $table->integer('orders_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
