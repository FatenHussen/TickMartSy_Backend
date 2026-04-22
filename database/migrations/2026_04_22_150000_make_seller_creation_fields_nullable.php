<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->change();
            $table->unsignedBigInteger('city_id')->nullable()->change();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('owner_phone')->nullable()->change();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->string('mobile')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('mobile')->nullable(false)->change();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('owner_phone')->nullable(false)->change();
        });

        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable(false)->change();
            $table->unsignedBigInteger('governorate_id')->nullable(false)->change();
        });
    }
};
