<?php

use App\Enums\CustomOrderRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_address_id')->constrained('user_addresses')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->text('description');
            $table->json('images')->nullable();
            $table->timestamp('expected_at')->nullable();
            $table->string('status')->default(CustomOrderRequestStatus::PENDING_PRICING->value);
            $table->text('admin_note')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_requests');
    }
};
