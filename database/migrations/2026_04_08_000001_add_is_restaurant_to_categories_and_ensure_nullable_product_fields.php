<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_restaurant')) {
                $table->boolean('is_restaurant')->default(false)->after('is_active');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->change();
            }

            if (Schema::hasColumn('products', 'model')) {
                $table->string('model')->nullable()->change();
            }

            if (Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->change();
            }

            if (Schema::hasColumn('products', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->change();
            }

            if (Schema::hasColumn('products', 'sale_country_id')) {
                $table->unsignedBigInteger('sale_country_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'is_restaurant')) {
                $table->dropColumn('is_restaurant');
            }
        });
    }
};
