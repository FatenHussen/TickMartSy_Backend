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
             // default | recipe | admin_cart | scheduled_admin_cart

            /** Quantities & Delivery */
            $table->unsignedInteger('total_quantity')->default(0);
            $table->unsignedInteger('delivery_price')->default(0);

            /** Pricing (Before Discount) */
            $table->unsignedBigInteger('subtotal')->default(0);

            /** Discounts (Always Percentage) */
            $table->unsignedBigInteger('basket_discount')->default(0);
            $table->unsignedBigInteger('coupon_discount')->nullable();

            /** Info */
            $table->string('discount_source')->nullable();
            // product | recipe | admin_cart | scheduled_admin_cart | coupon

            /** Final */
            $table->unsignedBigInteger('total')->default(0);

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
