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
            $table->foreignId('shop_product_variant_id')->constrained()->cascadeOnDelete();

            $table->string('product_name');
            $table->json('variant_attributes')->nullable();

            $table->string('item_status')->default(OrderStatus::PENDING->value);

            $table->unsignedInteger('quantity')->default(1);

            /** Base price */
            $table->unsignedBigInteger('price');

            /** Product discount percentage (ONLY for default cart) */
            $table->unsignedBigInteger('discount')->default(0);


            $table->timestamp('pending_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('out_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

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
