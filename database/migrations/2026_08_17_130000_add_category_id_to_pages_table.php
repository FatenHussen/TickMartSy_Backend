<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every category (at any level) becomes its own customizable page in the Page
 * Builder. This links a page row to a category; deleting the category removes
 * its page (and, via page_sections cascade, its section links).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->unique()
                ->after('slug')
                ->constrained('categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
