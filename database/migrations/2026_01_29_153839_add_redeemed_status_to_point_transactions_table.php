<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite does not support modifying ENUM columns this way.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE point_transactions MODIFY COLUMN status ENUM('pending', 'earned', 'expired', 'redeemed') DEFAULT 'earned'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE point_transactions MODIFY COLUMN status ENUM('pending', 'earned', 'expired') DEFAULT 'earned'");
        }
    }
};