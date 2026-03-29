<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_id')->constrained('gifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('user_notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('user_addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gifts');
    }
};
