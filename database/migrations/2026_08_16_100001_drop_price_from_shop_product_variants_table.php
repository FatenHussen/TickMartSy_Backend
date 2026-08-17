<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * السعر والكمية صارا على مستوى الـ product variant، وما عاد في قيم خاصة بكل محل.
     */
    public function up(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('shop_product_variants', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('shop_product_variants', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * بيرجّع الأعمدة فاضية — القيم القديمة صارت على product_variants.
     */
    public function down(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_product_variants', 'quantity')) {
                $table->integer('quantity')->nullable();
            }

            if (!Schema::hasColumn('shop_product_variants', 'price')) {
                $table->double('price', 12, 2)->nullable()->after('quantity');
            }
        });
    }
};
