<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_extra_details')) {
            return;
        }

        Schema::table('product_extra_details', function (Blueprint $table) {
            if (!Schema::hasColumn('product_extra_details', 'price')) {
                $table->double('price', 12, 2)->default(0)->after('detail_value');
            }
        });

        // Description is optional; add-ons are name + price.
        Schema::table('product_extra_details', function (Blueprint $table) {
            if (Schema::hasColumn('product_extra_details', 'detail_value')) {
                $table->json('detail_value')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_extra_details')) {
            return;
        }

        Schema::table('product_extra_details', function (Blueprint $table) {
            if (Schema::hasColumn('product_extra_details', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
