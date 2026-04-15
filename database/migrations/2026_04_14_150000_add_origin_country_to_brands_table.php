<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('origin_country_id')
                ->nullable()
                ->after('category_id')
                ->constrained('countries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['origin_country_id']);
            $table->dropColumn('origin_country_id');
        });
    }
};
