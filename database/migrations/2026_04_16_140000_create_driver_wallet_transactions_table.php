<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['paid_by_user', 'paid_by_system']);
            $table->decimal('amount', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('rate_percent', 5, 2)->default(0);
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index(['driver_id', 'type']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_wallet_transactions');
    }
};
