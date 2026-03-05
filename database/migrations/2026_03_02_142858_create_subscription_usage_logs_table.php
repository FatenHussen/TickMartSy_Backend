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
        Schema::create('subscription_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // نوع الاستخدام: discount, free_delivery, points_bonus
            $table->enum('usage_type', ['discount', 'free_delivery', 'points_bonus']);

            // القيمة المستخدمة
            $table->decimal('value', 10, 2)->nullable()->comment('قيمة الخصم أو النقاط');

            // الرصيد قبل وبعد الاستخدام
            $table->integer('remaining_orders_before')->nullable();
            $table->integer('remaining_orders_after')->nullable();
            $table->integer('remaining_free_deliveries_before')->nullable();
            $table->integer('remaining_free_deliveries_after')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['subscription_id', 'usage_type']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_usage_logs');
    }
};
