<?php

use App\Enums\OrderStatus;
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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('product_id')->constrained();
            // $table->foreignId('product_variant_id')->constrained();
            $table->foreignId('shop_product_variant_id')->constrained('shop_product_variants')->cascadeOnDelete();
            $table->string('product_name');
            $table->json('variant_attributes')->nullable();
            $table->string('item_status')->default(OrderStatus::PENDING->value);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('discount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
