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
        Schema::table('vendor_notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('type');
            $table->foreignId('vendor_user_id')->nullable()->constrained('vendor_users')->onDelete('cascade')->after('notifiable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_notifications', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropForeignIdFor('vendor_user_id');
            $table->dropColumn('vendor_user_id');
        });
    }
};
