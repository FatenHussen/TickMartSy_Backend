<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('affiliate_visit_commission_enabled')
                ->default(false)
                ->after('affiliate_fixed_commission');

            $table->unsignedInteger('affiliate_visit_commission_threshold')
                ->nullable()
                ->after('affiliate_visit_commission_enabled');

            $table->decimal('affiliate_visit_commission_amount', 10, 2)
                ->nullable()
                ->after('affiliate_visit_commission_threshold');

            $table->unsignedInteger('affiliate_visit_rewarded_steps')
                ->default(0)
                ->after('affiliate_visit_commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_visit_commission_enabled',
                'affiliate_visit_commission_threshold',
                'affiliate_visit_commission_amount',
                'affiliate_visit_rewarded_steps',
            ]);
        });
    }
};

