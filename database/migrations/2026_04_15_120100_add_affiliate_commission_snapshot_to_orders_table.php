<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('affiliate_commission_type', [
                'percentage_order',
                'fixed_per_order',
                'percentage_selected_products',
            ])->nullable()->after('affiliate_source');

            $table->decimal('affiliate_fixed_commission', 10, 2)
                ->nullable()
                ->after('affiliate_commission_type');

            $table->decimal('affiliate_commission_amount', 12, 2)
                ->default(0)
                ->after('affiliate_fixed_commission');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_commission_type',
                'affiliate_fixed_commission',
                'affiliate_commission_amount',
            ]);
        });
    }
};

