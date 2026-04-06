<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('governorate_id')->nullable()->after('is_active')->constrained('governorates')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('governorate_id')->constrained('cities')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('city_id')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['governorate_id', 'city_id', 'category_id']);
        });
    }
};
