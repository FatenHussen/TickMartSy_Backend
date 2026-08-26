<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * عند حذف فئة مؤكَّد: المنتجات تُخفى (soft delete) ويُفك ارتباطها بالفئة.
 * category_id يصبح nullable + nullOnDelete حتى لا يمنع restrict الحذف.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // امنع القيم الفارغة قبل إرجاع NOT NULL
        DB::table('products')->whereNull('category_id')->delete();

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }
};
