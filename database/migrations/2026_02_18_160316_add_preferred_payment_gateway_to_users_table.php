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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('preferred_payment_method_id')->nullable()->after('image')->constrained('payment_methods')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'preferred_payment_method_id')) {
                $table->dropForeign(['preferred_payment_method_id']);
                $table->dropColumn('preferred_payment_method_id');
            }
            if (Schema::hasColumn('users', 'preferred_payment_gateway')) {
                $table->dropColumn('preferred_payment_gateway');
            }
        });
    }
};
