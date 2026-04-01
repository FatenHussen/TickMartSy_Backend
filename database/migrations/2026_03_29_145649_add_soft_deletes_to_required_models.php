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
        // Add deleted_at to users table
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to drivers table (replace is_deleted)
        if (Schema::hasColumn('drivers', 'is_deleted')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
        if (!Schema::hasColumn('drivers', 'deleted_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to vendor_users table
        if (!Schema::hasColumn('vendor_users', 'deleted_at')) {
            Schema::table('vendor_users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to admins table
        if (!Schema::hasColumn('admins', 'deleted_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to orders table
        if (!Schema::hasColumn('orders', 'deleted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to shops table
        if (!Schema::hasColumn('shops', 'deleted_at')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to vendors table
        if (!Schema::hasColumn('vendors', 'deleted_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove deleted_at from users
        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove deleted_at from drivers and restore is_deleted
        if (Schema::hasColumn('drivers', 'deleted_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        if (!Schema::hasColumn('drivers', 'is_deleted')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false);
            });
        }

        // Remove deleted_at from vendor_users
        if (Schema::hasColumn('vendor_users', 'deleted_at')) {
            Schema::table('vendor_users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove deleted_at from admins
        if (Schema::hasColumn('admins', 'deleted_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove deleted_at from orders
        if (Schema::hasColumn('orders', 'deleted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove deleted_at from shops
        if (Schema::hasColumn('shops', 'deleted_at')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove deleted_at from vendors
        if (Schema::hasColumn('vendors', 'deleted_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
