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
        Schema::table('products', function (Blueprint $table) {
            // إزالة حقل country القديم (JSON)
            $table->dropColumn('country');

            // إضافة الحقول الجديدة
            $table->foreignId('country_id')->nullable()->after('model')->constrained('countries')->nullOnDelete();
            $table->foreignId('country_sale_id')->nullable()->after('country_id')->constrained('sale_countries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['country_sale_id']);
            $table->dropColumn(['country_id', 'country_sale_id']);

            // إعادة حقل country القديم
            $table->json('country')->nullable();
        });
    }
};
