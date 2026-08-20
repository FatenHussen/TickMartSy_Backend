<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * layout = how the section is presented (slider / list / grid)
 * variant = card shape inside the section (horizontal / vertical / square)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_sections') && !Schema::hasColumn('page_sections', 'layout')) {
            Schema::table('page_sections', function (Blueprint $table) {
                $table->string('layout', 32)->default('slider')->after('variant');
            });
        }

        if (Schema::hasTable('sections') && !Schema::hasColumn('sections', 'layout')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->string('layout', 32)->default('slider')->after('variant');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_sections') && Schema::hasColumn('page_sections', 'layout')) {
            Schema::table('page_sections', function (Blueprint $table) {
                $table->dropColumn('layout');
            });
        }

        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'layout')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('layout');
            });
        }
    }
};
