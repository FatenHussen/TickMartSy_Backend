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
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('settlement_cycle', ['weekly', 'monthly'])
                ->default('weekly')
                ->after('commission_rate');
            $table->unsignedTinyInteger('settlement_day_of_week')
                ->nullable()
                ->after('settlement_cycle');
            $table->unsignedTinyInteger('settlement_day_of_month')
                ->nullable()
                ->after('settlement_day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_cycle',
                'settlement_day_of_week',
                'settlement_day_of_month',
            ]);
        });
    }
};
