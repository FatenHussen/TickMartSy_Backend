<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_basket_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('basket_type', 50);
            $table->unsignedBigInteger('basket_reference_id');
            $table->foreignId('basket_schedule_id')->nullable()->constrained('basket_schedules')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('alert_type', 50)->default('stock_issue');
            $table->string('status', 50)->default('open');
            $table->string('user_decision', 50)->nullable();
            $table->json('payload')->nullable();
            $table->date('next_run_date')->nullable();
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_resolution_notified_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'basket_type', 'basket_reference_id'], 'scheduled_basket_alerts_lookup_idx');
            $table->index(['status', 'alert_type'], 'scheduled_basket_alerts_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_basket_alerts');
    }
};
