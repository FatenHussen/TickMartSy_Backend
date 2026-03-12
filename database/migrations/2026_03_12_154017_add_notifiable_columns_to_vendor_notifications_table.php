<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('vendor_notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');
            }
        });

        // Update existing records
        DB::table('vendor_notifications')
            ->whereNull('notifiable_type')
            ->update([
                'notifiable_type' => 'App\\Models\\VendorUser',
                'notifiable_id' => DB::raw('vendor_user_id')
            ]);
    }

    public function down(): void
    {
        Schema::table('vendor_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_notifications', 'notifiable_type')) {
                $table->dropColumn('notifiable_type');
            }
            if (Schema::hasColumn('vendor_notifications', 'notifiable_id')) {
                $table->dropColumn('notifiable_id');
            }
        });
    }
};
