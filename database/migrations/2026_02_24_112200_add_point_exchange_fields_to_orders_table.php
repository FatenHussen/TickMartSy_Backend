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
        Schema::table('orders', function (Blueprint $table) {
            // Point exchange fields
            $table->foreignId('used_coupon_exchange_id')->nullable()->constrained('point_exchanges')->nullOnDelete();
            $table->foreignId('used_free_delivery_exchange_id')->nullable()->constrained('point_exchanges')->nullOnDelete();
            $table->decimal('coupon_discount_from_points', 10, 2)->nullable()->default(0);
            $table->boolean('free_delivery_from_points')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['used_coupon_exchange_id']);
            $table->dropForeign(['used_free_delivery_exchange_id']);
            $table->dropColumn([
                'used_coupon_exchange_id',
                'used_free_delivery_exchange_id',
                'coupon_discount_from_points',
                'free_delivery_from_points'
            ]);
        });
    }
};
