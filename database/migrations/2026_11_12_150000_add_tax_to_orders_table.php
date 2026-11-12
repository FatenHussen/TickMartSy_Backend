<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tax is shown as its own line in the order summary, so it is stored with the
 * order: the rate can change later and an order is a historical record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('tax')->default(0)->after('total');
            $table->float('tax_rate')->default(0)->after('tax');
        });

        // Default 0, so the tax line stays hidden until the dashboard sets a rate.
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'tikmart_tax_rate'],
                [
                    'value' => '0',
                    'type' => 'number',
                    'group' => 'orders',
                    'title' => 'Order tax rate (%)',
                    'description' => 'Percentage charged on goods after discounts. 0 hides the tax line.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tax', 'tax_rate']);
        });
    }
};
