<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add stock & max_purchase_quantity to products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock')) {
                $table->unsignedInteger('stock')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('products', 'max_purchase_quantity')) {
                $table->unsignedInteger('max_purchase_quantity')->nullable()->after('stock');
            }
        });

        // Add name & sku to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'name')) {
                $table->string('name')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('product_variants', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'max_purchase_quantity']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['name', 'sku']);
        });
    }
};
