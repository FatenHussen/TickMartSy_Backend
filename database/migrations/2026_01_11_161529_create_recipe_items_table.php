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
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipe_id')
                ->constrained('recipes')
                ->cascadeOnDelete();

            $table->foreignId('shop_product_variant_id')
                ->constrained('shop_product_variants')
                ->cascadeOnDelete();

            $table->foreignId('switchable_category_id')->nullable()->constrained('categories');

            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->default(10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_products');
    }
};
