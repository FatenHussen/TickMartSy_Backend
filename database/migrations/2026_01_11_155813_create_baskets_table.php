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
        Schema::create('baskets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->json('name');
            $table->integer('num_varieties');
            $table->date('offer_ends_at')->nullable();
            $table->decimal('price', 8, 2); 
            $table->decimal('discount', 8, 2)->default(0); 
            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage'); 
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('num_sold')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_schedule')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baskets');
    }
};
