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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->json('name');
            $table->json('description');
            $table->json('full_description');
            $table->string('sku')->nullable()->unique();
            $table->json('country')->nullable();
            $table->string('model')->nullable()->unique();
            $table->integer('price');
            $table->integer('price_after_discount');
            $table->integer('quantity')->nullable();
            $table->string('barcode')->nullable();
            $table->time('time_prepare')->nullable();
            $table->json('bought_with')->nullable();
            $table->boolean('is_instant_delivery')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
