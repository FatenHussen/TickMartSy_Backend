<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'discount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->double('discount', 12, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'discount')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->double('discount', 12, 2)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'discount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('discount')->default(0)->change();
            });
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'discount')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unsignedInteger('discount')->nullable()->change();
            });
        }
    }
};
