<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The cart now lives on the server and follows the account across devices,
 * so everything needed to rebuild the cart screen on a second device has to
 * survive here: the extras and the cart type with its linked id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('extras')->nullable()->after('note');
            $table->string('cart_type')->default('default')->after('extras');
            $table->unsignedBigInteger('recipe_id')->nullable()->after('cart_type');
            $table->unsignedBigInteger('admin_basket_id')->nullable()->after('recipe_id');
            $table->unsignedBigInteger('admin_schedule_basket_id')->nullable()->after('admin_basket_id');
            $table->unsignedBigInteger('basket_schedule_id')->nullable()->after('admin_schedule_basket_id');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn([
                'extras',
                'cart_type',
                'recipe_id',
                'admin_basket_id',
                'admin_schedule_basket_id',
                'basket_schedule_id',
            ]);
        });
    }
};
