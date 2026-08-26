<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sale_channel', 20)
                ->default('platform')
                ->after('vendor_id')
                ->comment('platform = site catalog; shop = linked to vendor branch');
        });

        // Existing Tikmool products → platform; other vendors → shop
        DB::table('products')->where('vendor_id', 1)->update(['sale_channel' => 'platform']);
        DB::table('products')->where('vendor_id', '!=', 1)->update(['sale_channel' => 'shop']);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_channel');
        });
    }
};
