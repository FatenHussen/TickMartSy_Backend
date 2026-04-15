<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'commission_type')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->enum('commission_type', ['percentage', 'fixed'])
                    ->default('percentage')
                    ->after('commission_rate');
            });
        }

        if (!Schema::hasColumn('vendors', 'fixed_commission')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->decimal('fixed_commission', 10, 2)
                    ->default(0)
                    ->after('commission_type');
            });
        }

        if (!Schema::hasColumn('vendors', 'settlement_cycle')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->enum('settlement_cycle', ['weekly', 'monthly'])
                    ->default('monthly')
                    ->after('fixed_commission');
            });
        }
    }

    public function down(): void
    {
        $columns = [];

        foreach (['commission_type', 'fixed_commission', 'settlement_cycle'] as $column) {
            if (Schema::hasColumn('vendors', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table('vendors', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
