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
            $table->foreignId('subscription_id')->nullable()->after('affiliate_source')->constrained()->nullOnDelete();
            $table->decimal('subscription_discount', 10, 2)->default(0)->after('subscription_id');
            $table->boolean('subscription_free_delivery')->default(false)->after('subscription_discount');
            $table->integer('subscription_points_bonus')->default(0)->after('subscription_free_delivery');
            $table->foreignId('promotion_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('promotion_discount', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn([
                'subscription_id',
                'subscription_discount',
                'subscription_free_delivery',
                'subscription_points_bonus',
            ]);
        });
    }
};
