<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('vendor_service_id')->constrained('vendor_services')->cascadeOnDelete();
            $table->foreignId('shop_vendor_service_id')->nullable()->constrained('shop_vendor_services')->nullOnDelete();

            $table->decimal('price', 12, 2)->default(0);
            $table->string('price_unit')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['shop_id', 'vendor_service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
