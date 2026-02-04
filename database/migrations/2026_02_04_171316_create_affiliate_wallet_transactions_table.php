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
        Schema::create('affiliate_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('affiliate_id');
            $table->enum('type', ['commission', 'withdraw'])->default('commission');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // مرتبط بطلب أو لا
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_wallet_transactions');
    }
};
