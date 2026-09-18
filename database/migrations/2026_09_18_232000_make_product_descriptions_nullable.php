<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * description is translatable JSON, optional on create.
     * Keep json (not text) — changing type would break Spatie translations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'description')) {
                $table->json('description')->nullable()->change();
            }

            if (Schema::hasColumn('products', 'full_description')) {
                $table->json('full_description')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'description')) {
                $table->json('description')->nullable(false)->change();
            }
        });
    }
};
