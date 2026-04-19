<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promotions')) {
            return;
        }

        DB::table('promotions')->where('type', 'buy_x_get_y')->delete();

        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'buy_quantity')) {
                $table->dropColumn(['buy_quantity', 'get_quantity']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('promotions')) {
            return;
        }

        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'buy_quantity')) {
                $table->integer('buy_quantity')->nullable();
                $table->integer('get_quantity')->nullable();
            }
        });
    }
};
