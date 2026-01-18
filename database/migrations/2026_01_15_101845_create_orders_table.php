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
            $table->foreignId('driver_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_address_id')->constrained('user_addresses')->cascadeOnDelete();
            $table->boolean('is_instant_delivery')->default(false);
            $table->string('order_status')->default(OrderStatus::PENDING->value);
            $table->string('cart_type')->default(CartType::DEFAULT->value);
            $table->unsignedInteger('delivery_price')->default(0);
            $table->unsignedInteger('total_quantity')->default(1);
            $table->unsignedInteger('total')->nullable();
            $table->unsignedInteger('coupon_discount')->nullable();
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
