<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_actions', function (Blueprint $table) {
            $table->json('button_text')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('quick_actions', function (Blueprint $table) {
            $table->dropColumn('button_text');
        });
    }
};
