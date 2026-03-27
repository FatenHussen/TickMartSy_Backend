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
            // Remove old country field (was JSON)
            $table->dropColumn('country');

            // Add new country fields as foreign keys
            $table->foreignId('country_id')->nullable()->after('model')->constrained('countries')->nullOnDelete();
            $table->foreignId('country_sale_id')->nullable()->after('country_id')->constrained('countries')->nullOnDelete();
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

            // Restore old country field
            $table->json('country')->nullable();
        });
    }
};
