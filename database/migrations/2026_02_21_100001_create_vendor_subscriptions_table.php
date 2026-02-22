<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_package_id')->constrained()->cascadeOnDelete();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('auto_renew')->default(false);
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->timestamp('notified_at')->nullable(); // آخر إشعار باقتراب الانتهاء
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['ends_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_subscriptions');
    }
};
