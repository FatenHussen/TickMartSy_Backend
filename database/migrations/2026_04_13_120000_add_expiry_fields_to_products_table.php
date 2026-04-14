<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->date('expiry_date')->nullable()->after('warranty_period');
            $table->timestamp('expiry_notified_at')->nullable()->after('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['expiry_date', 'expiry_notified_at']);
        });
    }
};
