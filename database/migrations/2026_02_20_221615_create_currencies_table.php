<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, SYP, AED
            $table->json('name'); // {"en": "US Dollar", "ar": "دولار أمريكي"}
            $table->string('symbol', 10); // $, ل.س, د.إ
            $table->decimal('exchange_rate', 15, 6)->default(1); // مقابل الدولار
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // إضافة عمود العملة للمستخدمين
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->default(1)->constrained('currencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });

        Schema::dropIfExists('currencies');
    }
};
