<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_packages', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Basic, Pro, Premium
            $table->json('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_days')->default(30); // مدة الباقة
            $table->boolean('is_active')->default(true);

            // 1. Product Management
            $table->unsignedInteger('max_products')->default(50);

            // 2. Marketing & Visibility
            $table->boolean('is_featured')->default(false);
            $table->boolean('has_premium_badge')->default(false);
            $table->unsignedTinyInteger('search_priority')->default(1); // 1-10 أعلى = أفضل ظهور

            // 3. Offers & Promotions
            $table->unsignedInteger('max_campaigns')->default(0);
            $table->boolean('has_banner_ad')->default(false);

            // 4. Reports & Analytics
            $table->boolean('has_sales_reports')->default(true);
            $table->boolean('has_analytics')->default(false);
            $table->string('report_level', 20)->default('basic'); // basic, advanced, full

            // 5. Order & Delivery
            $table->unsignedTinyInteger('order_priority')->default(1); // 1-10 أعلى = أولوية
            $table->boolean('can_set_prep_time')->default(false);
            $table->boolean('custom_shipping_options')->default(false);
            $table->boolean('has_vendor_delivery')->default(false);

            // 6. Commission & Fees
            $table->decimal('commission_rate', 5, 2)->default(5.00); // نسبة العمولة
            $table->decimal('commission_per_order', 8, 2)->default(0); // رسوم لكل طلب
            $table->boolean('activation_fee_waived')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_packages');
    }
};
