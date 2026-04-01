<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('recipes', 'is_active')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('orders_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('recipes', 'is_active')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
