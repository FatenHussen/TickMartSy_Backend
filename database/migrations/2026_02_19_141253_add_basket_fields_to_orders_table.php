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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('basket_id')->nullable()->after('user_address_id')->constrained('baskets')->nullOnDelete();
            $table->foreignId('basket_schedule_id')->nullable()->after('basket_id')->constrained('basket_schedules')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['basket_id']);
            $table->dropForeign(['basket_schedule_id']);
            $table->dropColumn(['basket_id', 'basket_schedule_id']);
        });
    }
};
