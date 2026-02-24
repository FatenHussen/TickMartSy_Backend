<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('point_transactions')->cascadeOnDelete();

            $table->enum('exchange_type', ['coupon', 'free_delivery', 'gift']);
            $table->json('exchange_data'); // Store specific data for each type
            $table->enum('status', ['pending', 'completed', 'cancelled','used'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'exchange_type']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_exchanges');
    }
};
