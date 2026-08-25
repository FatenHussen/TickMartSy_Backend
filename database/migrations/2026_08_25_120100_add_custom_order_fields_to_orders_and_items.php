<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('custom_order_request_id')
                ->nullable()
                ->after('payment_method_id')
                ->constrained('custom_order_requests')
                ->nullOnDelete();

            $table->boolean('has_external_items')->default(false)->after('is_paid');
            $table->string('price_variance_type')->nullable()->after('has_external_items');
            $table->unsignedBigInteger('price_variance_value')->nullable()->after('price_variance_type');
            $table->unsignedBigInteger('approximate_total')->nullable()->after('price_variance_value');
            $table->timestamp('waiting_approval_at')->nullable()->after('pending_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['shop_product_variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_product_variant_id')->nullable()->change();
            $table->boolean('is_external')->default(false)->after('shop_product_variant_id');
            $table->string('invoice_image')->nullable()->after('product_image');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('shop_product_variant_id')
                ->references('id')
                ->on('shop_product_variants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['shop_product_variant_id']);
            $table->dropColumn(['is_external', 'invoice_image']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_product_variant_id')->nullable(false)->change();
            $table->foreign('shop_product_variant_id')
                ->references('id')
                ->on('shop_product_variants')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['custom_order_request_id']);
            $table->dropColumn([
                'custom_order_request_id',
                'has_external_items',
                'price_variance_type',
                'price_variance_value',
                'approximate_total',
                'waiting_approval_at',
            ]);
        });
    }
};
