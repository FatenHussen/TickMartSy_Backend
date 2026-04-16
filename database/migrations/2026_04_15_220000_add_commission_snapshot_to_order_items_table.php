<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'commission_snapshot_type')) {
                $table->enum('commission_snapshot_type', ['percentage', 'fixed'])
                    ->default('percentage')
                    ->after('total');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_rate')) {
                $table->decimal('commission_snapshot_rate', 8, 2)
                    ->default(0)
                    ->after('commission_snapshot_type');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_fixed')) {
                $table->decimal('commission_snapshot_fixed', 12, 2)
                    ->default(0)
                    ->after('commission_snapshot_rate');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_amount')) {
                $table->decimal('commission_snapshot_amount', 12, 2)
                    ->default(0)
                    ->after('commission_snapshot_fixed');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_source')) {
                $table->enum('commission_snapshot_source', ['vendor', 'package'])
                    ->default('vendor')
                    ->after('commission_snapshot_amount');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_package_id')) {
                $table->unsignedBigInteger('commission_snapshot_package_id')
                    ->nullable()
                    ->after('commission_snapshot_source');
            }

            if (!Schema::hasColumn('order_items', 'commission_snapshot_package_name')) {
                $table->string('commission_snapshot_package_name')
                    ->nullable()
                    ->after('commission_snapshot_package_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $columns = [
                'commission_snapshot_type',
                'commission_snapshot_rate',
                'commission_snapshot_fixed',
                'commission_snapshot_amount',
                'commission_snapshot_source',
                'commission_snapshot_package_id',
                'commission_snapshot_package_name',
            ];

            $existingColumns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('order_items', $column)));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
