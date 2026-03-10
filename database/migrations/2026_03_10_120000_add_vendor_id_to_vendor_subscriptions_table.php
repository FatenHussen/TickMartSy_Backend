<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vendor_subscriptions', 'vendor_id')) {
            Schema::table('vendor_subscriptions', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('id');
                $table->index(['vendor_id', 'status']);
                $table->index(['ends_at', 'status']);
            });
        }

        if (Schema::hasColumn('vendor_subscriptions', 'vendor_id') && Schema::hasColumn('vendor_subscriptions', 'shop_id')) {
            DB::table('vendor_subscriptions as vs')
                ->join('shops as s', 'vs.shop_id', '=', 's.id')
                ->whereNull('vs.vendor_id')
                ->update(['vs.vendor_id' => DB::raw('s.vendor_id')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vendor_subscriptions', 'vendor_id')) {
            Schema::table('vendor_subscriptions', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }
    }
};
