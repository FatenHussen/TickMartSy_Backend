<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_methods', function (Blueprint $table) {
            $table->string('key')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_methods', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
