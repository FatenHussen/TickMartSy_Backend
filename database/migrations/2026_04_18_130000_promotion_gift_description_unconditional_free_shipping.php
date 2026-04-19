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

        DB::table('promotions')->where('type', 'free_shipping')->update(['min_spend' => null]);

        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'gift_product_ids')) {
                $table->dropColumn('gift_product_ids');
            }
        });

        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'gift_description')) {
                $after = Schema::hasColumn('promotions', 'reward_points') ? 'reward_points' : 'discount_type';
                $table->json('gift_description')->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('promotions')) {
            return;
        }

        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'gift_description')) {
                $table->dropColumn('gift_description');
            }
        });

        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'gift_product_ids')) {
                $table->json('gift_product_ids')->nullable();
            }
        });
    }
};
