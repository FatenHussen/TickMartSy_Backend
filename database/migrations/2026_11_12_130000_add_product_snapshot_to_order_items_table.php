<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An order is a historical record: the dashboard must show what was bought,
 * not what the catalogue says today. Product id, SKU, model and the stock that
 * was available are snapshotted alongside the name, image and attributes that
 * order_items already stores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('shop_product_variant_id');
            $table->string('sku')->nullable()->after('product_image');
            $table->string('model')->nullable()->after('sku');
            $table->unsignedInteger('available_quantity')->nullable()->after('model');
            $table->string('shop_name')->nullable()->after('available_quantity');
            $table->unsignedBigInteger('shop_id')->nullable()->after('shop_name');

            $table->index('product_id');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['shop_id']);
            $table->dropColumn([
                'product_id',
                'sku',
                'model',
                'available_quantity',
                'shop_name',
                'shop_id',
            ]);
        });
    }
};
