<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE affiliate_wallet_transactions MODIFY COLUMN type ENUM('commission','withdraw','visit_commission') NOT NULL DEFAULT 'commission'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE affiliate_wallet_transactions MODIFY COLUMN type ENUM('commission','withdraw') NOT NULL DEFAULT 'commission'");
    }
};

