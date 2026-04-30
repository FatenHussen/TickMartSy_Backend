<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old table if exists with product_id
        Schema::dropIfExists('product_extra_details');

        // Create new generic extra details table
        Schema::create('product_extra_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->json('detail_key');
            $table->json('detail_value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create pivot table for many-to-many relationship with extra data
        Schema::create('product_extra_detail_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_extra_detail_id')->constrained('product_extra_details')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'product_extra_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_extra_detail_options');
        Schema::dropIfExists('product_extra_details');
    }
};
