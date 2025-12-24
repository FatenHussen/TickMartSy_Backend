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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // ar, en, fr, etc.
            $table->string('name'); // Arabic, English, French
            $table->string('native_name')->nullable(); // العربية, English, Français
            $table->string('direction', 3)->default('ltr'); // ltr, rtl
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('order')->default(0);
            $table->string('flag_icon')->nullable(); // 🇸🇦, 🇺🇸, 🇫🇷
            $table->string('locale', 10)->nullable(); // ar_SA, en_US, fr_FR
            $table->string('timezone')->nullable(); // Asia/Riyadh, America/New_York
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->string('decimal_separator')->default('.');
            $table->string('thousands_separator')->default(',');
            $table->string('currency_code', 3)->nullable(); // SAR, USD, EUR
            $table->string('currency_symbol')->nullable(); // ﷼, $, €
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_switcher')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
