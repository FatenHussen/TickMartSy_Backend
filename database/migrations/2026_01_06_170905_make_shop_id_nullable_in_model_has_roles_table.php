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
        // Schema::table('model_has_roles', function (Blueprint $table) {
        //     $table->unsignedBigInteger('shop_id')->default(0)->change();
        // });

        // Schema::table('model_has_permissions', function (Blueprint $table) {
        //     $table->unsignedBigInteger('shop_id')->default(0)->change();
        // });
    }

    public function down(): void {}
};
