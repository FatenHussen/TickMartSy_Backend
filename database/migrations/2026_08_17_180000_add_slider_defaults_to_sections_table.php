<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slider defaults live on the Section itself so a slider can be created
 * without choosing a page. When it is later attached to a page, these
 * values are copied onto the page_section (and can still be overridden).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('variant')->default('horizontal')->after('manual_model');
            $table->string('background_color')->nullable()->after('variant');
            $table->string('background_card_color')->nullable()->after('background_color');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['variant', 'background_color', 'background_card_color']);
        });
    }
};
