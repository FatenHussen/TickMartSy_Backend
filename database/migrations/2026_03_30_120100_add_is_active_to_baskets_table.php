<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('baskets', 'is_active')) {
            Schema::table('baskets', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('is_schedule');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('baskets', 'is_active')) {
            Schema::table('baskets', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
