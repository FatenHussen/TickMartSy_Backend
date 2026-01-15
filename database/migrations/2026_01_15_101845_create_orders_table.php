<?php

use App\Enums\CartType;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_instant_delivery')->default(false);
            $table->string('order_status')->default(OrderStatus::PENDING->value);
            $table->string('cart_type')->default(CartType::DEFAULT->value);
            $table->integer('delivery_price')->default(0);
            $table->unsignedInteger('num_items')->default(1);
            $table->integer('price')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('coupon_discount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
