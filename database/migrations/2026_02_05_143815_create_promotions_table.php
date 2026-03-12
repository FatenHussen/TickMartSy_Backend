<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->json('description')->nullable();

            // نوع العرض
            $table->string('type');
            // buy_x_get_y
            // spend_x_discount
            // spend_x_gift
            // simple_discount

            // حالة التفعيل
            $table->boolean('is_active')->default(true);

            // فترة الصلاحية
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fields used depending on type
            |--------------------------------------------------------------------------
            */

            // الحد الأدنى للشراء (لـ spend_x_*)
            $table->decimal('min_spend', 10, 2)->nullable();

            // buy_x_get_y
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();

            // الخصم
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();

            // هدايا (spend_x_gift)
            $table->json('gift_product_ids')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
