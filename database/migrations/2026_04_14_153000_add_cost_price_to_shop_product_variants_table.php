<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->integer('cost_price')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
