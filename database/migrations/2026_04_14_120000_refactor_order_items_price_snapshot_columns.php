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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->default(0)->after('price');
            $table->decimal('final_price', 12, 2)->default(0)->after('unit_price');
            $table->decimal('subtotal', 12, 2)->default(0)->after('final_price');
            $table->decimal('extras_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->after('extras_total');
            $table->dropColumn('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('discount')->default(0)->after('price');
            $table->dropColumn(['unit_price', 'final_price', 'subtotal', 'extras_total', 'total']);
        });
    }
};
