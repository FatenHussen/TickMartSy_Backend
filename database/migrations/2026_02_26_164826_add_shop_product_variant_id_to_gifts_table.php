<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->foreignId('shop_product_variant_id')->nullable()->after('category_id')->constrained('shop_product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropForeign(['shop_product_variant_id']);
            $table->dropColumn('shop_product_variant_id');
        });
    }
};
