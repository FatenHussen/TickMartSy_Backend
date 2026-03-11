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
            // أضف body إذا ما كان موجود
            if (!Schema::hasColumns('vendor_notifications', ['body','title'])) {
                $table->text('title')->nullable();

                $table->text('body')->nullable();
            }

            // أضف vendor_user_id فقط إذا ما كان موجود
            if (!Schema::hasColumn('vendor_notifications', 'vendor_user_id')) {
                $table->foreignId('vendor_user_id')->nullable()->constrained('vendor_users')->onDelete('cascade')->after('notifiable_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_notifications', 'body')) {
                $table->dropColumn('body');
            }

            if (Schema::hasColumn('vendor_notifications', 'vendor_user_id')) {
                $table->dropForeignIdFor('vendor_user_id');
                $table->dropColumn('vendor_user_id');
            }
        });
    }
};
