<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('commission_type', ['percentage', 'fixed'])
                ->default('percentage')
                ->after('commission_rate');

            $table->decimal('fixed_commission', 10, 2)
                ->default(0)
                ->after('commission_type');

            $table->enum('settlement_cycle', ['weekly', 'monthly'])
                ->default('monthly')
                ->after('fixed_commission');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'fixed_commission', 'settlement_cycle']);
        });
    }
};
