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
        // Add is_active to countries table
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('code');
        });

        // Add is_active to brands table
        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('image');
        });

        // Add is_active to banners table
        Schema::table('banners', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('link');
        });

        // Add is_active to faqs table
        Schema::table('faqs', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('type');
        });

        // Add is_active to services table
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });

        // Add is_active to areas table
        Schema::table('areas', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('city_id');
        });

        // Add is_active to cities table
        Schema::table('cities', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('governorate_id');
        });

        // Add is_active to governorates table
        Schema::table('governorates', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });


        // Add is_active to colors table
        Schema::table('colors', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('hex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('governorates', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });


        Schema::table('colors', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
