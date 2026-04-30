<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            ['roles', 'guard_name'],
            ['category_attributes', 'type'],
            ['category_details', 'name'],
            ['sections', 'details_slug'],
            ['page_sections', 'filters'],
            ['products', 'is_visible'],
            ['product_category_details', 'detail_value'],
            ['product_extra_details', 'price'],
            ['product_media', 'order'],
            ['product_variants', 'is_trend'],
            ['point_exchanges', 'status'],
            ['point_wallets', 'balance'],
            ['point_transactions', 'status'],
            ['vendor_subscriptions', 'status'],
            ['seller_registrations', 'status'],
            ['settings', 'type'],
            ['activity_logs', 'changes'],
            ['affiliate_withdraw_requests', 'status'],
            ['promotion_requests', 'status'],
            ['subscriptions', 'status'],
        ];

        foreach ($tables as [$table, $after]) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($after) {
                $tableBlueprint->boolean('is_active')->default(true)->after($after);
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'roles',
            'category_attributes',
            'category_details',
            'sections',
            'page_sections',
            'products',
            'product_category_details',
            'product_media',
            'product_variants',
            'point_exchanges',
            'point_wallets',
            'point_transactions',
            'vendor_subscriptions',
            'seller_registrations',
            'settings',
            'activity_logs',
            'affiliate_withdraw_requests',
            'promotion_requests',
            'subscriptions',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn('is_active');
            });
        }
    }
};
