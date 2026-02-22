<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id', 'status']);
        });

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable()->after('id');
        });

        // نقل الاشتراكات لأول شوب لكل فيندور
        $subs = DB::table('vendor_subscriptions')->get();
        foreach ($subs as $sub) {
            $firstShop = DB::table('shops')->where('vendor_id', $sub->vendor_id)->orderBy('id')->first();
            if ($firstShop) {
                DB::table('vendor_subscriptions')->where('id', $sub->id)->update(['shop_id' => $firstShop->id]);
            }
        }

        DB::table('vendor_subscriptions')->whereNull('shop_id')->delete();

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->index(['shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropIndex(['shop_id', 'status']);
        });

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('id');
        });

        $subs = DB::table('vendor_subscriptions')->get();
        foreach ($subs as $sub) {
            $shop = DB::table('shops')->find($sub->shop_id);
            if ($shop) {
                DB::table('vendor_subscriptions')->where('id', $sub->id)->update(['vendor_id' => $shop->vendor_id]);
            }
        }

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->index(['vendor_id', 'status']);
            $table->dropColumn('shop_id');
        });
    }
};
